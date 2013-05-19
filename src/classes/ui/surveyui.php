<?php

/*-------------------------------------------------------+
 | KDE Commit-Digest
 | Copyright 2010-2011 Danny Allen <danny@commit-digest.org>
 | http://www.commit-digest.org/
 +--------------------------------------------------------+
 | This program is released as free software under the
 | Affero GPL license. You can redistribute it and/or
 | modify it under the terms of this license which you
 | can read by viewing the included agpl.txt or online
 | at www.gnu.org/licenses/agpl.html. Removal of this
 | copyright header is strictly prohibited without
 | written permission from the original author(s).
 +--------------------------------------------------------*/


class SurveyUi {
  public $id            = 'survey';
  public $title         = null;

  public $noFrame       = false;

  private $developer    = null;


  public function __construct() {
    // set title
    $this->title      = _('Survey');

    // set display modes
    if (isset($_REQUEST['noFrame'])) {
      $this->noFrame = true;
    }

    // set section titles
    $this->sections  = array(1 => 'Motivation',
                                  'Motivation (continued)',
                                  'Your Experience',
                                  'Your Experience (continued)');

    $this->intros    = array(1 => 'Please associate the term \'project\' in all subsequent questions to this project defined above.',
                                  'Please associate the term \'project\' in all subsequent questions to the project which you defined on the first page.',
                                  'Please associate the term \'project\' in all subsequent questions to the project which you defined on the first page.',
                                  'Please associate the term \'project\' in all subsequent questions to the project which you defined on the first page.');

    // set survey questions
    $this->questions[1]  = array(1 => 'I want other people to find out how good I really can be in software development.',
                                      'This project group has a great deal of personal meaning for me.',
                                      'It is important to me that this project shares my views on open source software.',
                                      'I see myself as extraverted, enthusiastic.',
                                      'It is fun participating in this project.',
                                      'I see myself as open to new experiences, complex.',
                                      'The reason I participate in this project is because of what it stands for, that is, its values.',
                                      'I am strongly motivated by the recognition I can earn from other people in this project.',
                                      'I see myself as critical, quarrelsome.',
                                      'When someone praises this project, it feels like a personal compliment.',
                                      'Participating in this project gives me a satisfying feeling.',
                                      'I see myself as sympathetic, warm.',
                                      'I want to show other developers in the community how good I really can be.',
                                      'I see myself as disorganized, careless.',
                                      'I am strongly motivated by the money I can earn through my participation in this project.');

    $this->questions[2]  = array(1 => 'I feel a sense of belonging toward this project group.',
                                      'The project shares my views and beliefs on open source software.',
                                      'I am keenly aware of the income goals I have for myself if I participate in this project.',
                                      'I see myself as dependable, self-disciplined.',
                                      'I have a strong positive feeling toward this project group.',
                                      'I like chocolate.',
                                      'I see myself as conventional, uncreative.',
                                      'It is important to me that I can promote my career prospects through my participation in this project.',
                                      'I am motivated to participate in this project because it gives me the possibility to earn respect for my work.',
                                      'I see myself as calm, emotionally stable.',
                                      'I enjoy working in this project.',
                                      'I see myself as anxious, easily upset.',
                                      'My personal values and those of the project are similar.',
                                      'I am motivated by the future income gains I can achieve through my participation in this project.',
                                      'When I talk about the project, I usually say \'we\' rather than \'they\'.',
                                      'I see myself as reserved, quiet.',
                                      'It is important to me that I can show my programming capabilities to potential new employers through participating in this project.');

    $this->questions[3]  = array(1 => array(
                                        'How many corporate sponsors has this project.',
                                        array(1 => '0', '1', '2-3', '4-5', '>5')
                                      ),
                                      'Many project members live in my area.',
                                      'I would feel a sense of loss if I could no longer work together with the members in this project.',
                                      'It is important to me that companies are involved in the development of this project.',
                                      'Some of the developers in this project are highly respected by other developers in the community.',
                                      'Most members of this project are very competent and approach their work very professional.',
                                      'It is important to me that I often see other members of this project face to face.',
                                      'I appreciate that members of this project are famous within the community.',
                                      'Corporate sponsors shape the development of this project.',
                                      'I can rely on the members of this project to help me constructively in accomplishing my work.',
                                      'Some members of this project are famous in the community.',
                                      'I often coincidentally see project members.',
                                      'I plan to make future contributions to this project.',
                                      'If I share my problems with others in this project I know they will respond constructively and caringly.',
                                      'I like that some members of this project have a strong standing in the community.',
                                      'Corporate sponsors provide support for this project.');

    $this->questions[4]  = array(1 => 'There are other project members living close to me.',
                                      'Members of this project team regard each other as trustworthy.',
                                      'I regularly watch soccer on TV.',
                                      'Some developers in this project have a strong standing in the community.',
                                      'The involvement of corporate sponsors in this project is important to me.',
                                      'I intend to continue participating in this project rather than discontinue my involvement.',
                                      'On this project team, I can talk freely with others about difficulties I am having and know that others are willing to listen.',
                                      'Corporate sponsors help the project to succeed.',
                                      'It is important to me that some developers in this project are respected by others in the community.',
                                      'If I could I would like to discontinue my participation in this project.',
                                      'I commonly see other project members for private purpose.',
                                      'Others in the community know some developers in this project for their competence.',
                                      'I trust and respect the members of this project.',
                                      'I welcome companies’ involvement in the development of this project.',
                                      'Members of this project have a sharing relationship with each other. I can freely share my ideas, feelings and hopes.',
                                      'I like that other members of this project live in my area.');

    // define answers
    $this->answer['experience']   = array(1 => 'strongly disagree',
                                          2 => 'disagree',
                                          3 => 'neutral',
                                          4 => 'agree',
                                          5 => 'strongly agree');
  }


  public function draw() {
    // check that an access code has been given and that it is valid
    if (empty($_REQUEST['code']) ||
        !($this->developer = new Developer($_REQUEST['code'], 'access_code', true)) ||
        !$this->developer->data) {

      // invalid code
      return 'Invalid access code';
    }


    // check that survey hasn't already been completed
    if ($this->developer->surveyDone) {
      return 'Survey already completed';
    }


    $q = 1;

    // draw
    $buf   = '<div id="survey">
                <div id="section-0" class="section">
                  <h1>' .
                    $this->title .
             '      <span>Optional</span>
                    <aside>Page <span>1</span> of 5</aside>
                  </h1>

                  <div class="intro">
                    <h2>Win a Google Nexus 4 phone for helping me with my research</h2>

                    <p>
                      Hi everybody,
                    </p>

                    <p>
                      My name is Andreas Schilling. I am a research assistant at the chair of Information Systems and Services at Bamberg University.<br />
                      In my research, I examine why developers stay committed in their open-source projects.
                    </p>

                    <p>
                      Could you please give me some quick feedback about your motivation and your experiences at KDE?<br />
                      I am asking you because the best way to find out more about the factors which really matter to open-source developers is to ask them directly.<br />
                    </p>

                    <p>
                      For your feedback, please fill out the questions on the following pages – it will not take you more than 10 minutes.
                    </p>

                    <p>
                      You may be rewarded if you participate in this survey! From all survey participants (who have fully answered all questions), one random person will win the current Google Nexus 4 phone.
                    </p>

                    <p>
                      As soon as I finish my research, I will publish a blog post with the aggregated results of this survey, and I will compare them with the results of other surveys (such as with Google Summer of Code participants).
                    </p>

                    <p>
                      If you have any questions about this questionnaire or about my research, please do not hesitate to contact me directly (<a href="mailto:andreas.schilling@uni-bamberg.de">andreas.schilling@uni-bamberg.de</a>).
                    </p>

                    <p>
                      I would like to wish all of you the best for your coding projects at KDE.
                    </p>

                    <p>
                      Best Regards,<br />
                      Andreas Schilling
                    </p>

                    <button id="start-survey" onclick="startSurvey();">Start the survey</button>
                  </div>
                </div>

                <form id="survey_data" class="clearfix" action="" method="post">';

    foreach ($this->questions as $sectionNum => $section) {
      $buf  .= '  <div id="section-' . $sectionNum . '" class="section">';

      if ($sectionNum === 1) {
        $buf  .= '  <label class="project">
                      To which KDE project do you contribute/have you contributed to?
                      <span>If you contribute/contributed to more than one KDE project, please name the one which is most important to you.<span>
                      <input type="text" name="project" />
                    </label>';
      }

      $buf  .= '    <h3>' .
                      $this->sections[$sectionNum] .
               '    </h3>' .
               '    <p class="intro">' .
               '      <b>' . $this->intros[$sectionNum] . '</b><br />' .
               '      Rate your responses to the following questions using the radio button scale (strongly disagree to strongly agree)...
                    </p>

                    <table class="motivation">';

      foreach ($section as $num => $question) {
        if (is_array($question)) {
          // non-standard answer scale:
          // - end previous tbody block?
          if ($num > 1) {
            $buf  .= '  </tbody>';
          }

          // - draw new header section
          $buf  .= $this->drawHeader($question[1]) .
                   '  <tbody>';

          // - draw question
          $buf  .= $this->drawRow($q, $question[0], $question[1]);

          // - draw standard header section
          $buf  .= '  </tbody>' .
                      $this->drawHeader($this->answer['experience']) .
                   '  <tbody>';

        } else {
          // standard answer scale:
          // - draw header section?
          if ($num == 1) {
            $buf  .= $this->drawHeader($this->answer['experience']) .
                     '  <tbody>';
          }

          // - draw question
          $buf  .= $this->drawRow($q, $question, $this->answer['experience']);
        }

        // increment question number
        ++$q;
      }

      $buf  .= '      </tbody>
                    </table>
                  </div>';
    }

    $buf  .= '    <input id="access_code" name="access_code" type="hidden" value="' . $this->developer->access['code'] . '" />

                  <input id="submit" type="button" value="Submit my answers" onclick="submitSurvey(event);" />
                  <input id="next" type="button" value="Next page" onclick="nextPage(event);" />
                  <input id="prev" type="button" value="Previous page" onclick="previousPage(event);" />
                </form>

                <div id="tooltip" class="r" style="display:none;">
                </div>
              </div>

              <script type="text/javascript">
                // scroll to top of lightbox
                try {
                  $("lightwindow_contents").down("div.contents").scrollTop = 0;
                } catch(e) { }

                // setup radio button hovers
                $$("table.motivation label")
                  .invoke("observe", "mouseover", radioMouseover)
                  .invoke("observe", "mouseout", radioMouseout);

                // setup radio button clicks + keyboard interactions
                $$("table.motivation input.observe")
                  .invoke("observe", "change", radioClick)
                  .invoke("observe", "blur", radioMouseout)
                  .invoke("observe", "focus", radioMouseover);

                // hide sections
                $("section-2").hide();
                $("section-3").hide();
                $("section-4").hide();

                // setup page buttons
                $("prev").hide();
                if ($("prev")) {
                  $("prev").hide();
                }
                if ($("submit")) {
                  $("submit").hide();
                }
              </script>';

    return $buf;
  }


  public function drawHeader($scale) {
    $buf = '    <thead>
                  <tr>
                    <th class="q"></th>';

    foreach ($scale as $var => $answer) {
      $buf  .= '    <th class="a">' . $answer . '</th>';
    }

    $buf  .= '    </tr>
                </thead>';

    return $buf;
  }


  public function drawRow($q, $question, $titles) {
    $buf = '        <tr>
                      <td class="q">' . $question . '</td>';

    for ($i = 1; $i <= 5; $i++) {
      $buf  .= '      <td class="a">
                        <label class="r' . $i . '" title="' . $titles[$i] . '">
                          <input id="motivation-' . $q .'_' . $i . '" class="observe" type="radio" name="motivation-' . $q .'" value="' . $i . '" />
                        </label>
                      </td>';
    }

    $buf  .= '      </tr>';

    return $buf;
  }


  public function getScript() {
    return array('/js/frame/surveyui.js');
  }


  public function getStyle() {
    return array('/css/frame/surveyui.css');
  }
}

?>