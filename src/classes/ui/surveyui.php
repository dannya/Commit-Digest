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
  public $onlyContent   = false;

  private $developer    = null;


  public function __construct() {
    // set title
    $this->title      = _('Survey');

    // set display modes
    if (isset($_REQUEST['noFrame'])) {
      $this->noFrame = true;
    }
    if (isset($_REQUEST['onlyContent'])) {
      $this->onlyContent = true;
    }

    // set survey questions
    $this->questions = array(1 => 'What matters to me the most in participating in KDE is to tackle problems that are completely new to me',
                                  'I am strongly motivated by the recognition I can earn from other people in KDE',
                                  'I want other people to find out how good I really can be in software development/testing',
                                  'I am strongly motivated by the money I can earn through my participation in the KDE',
                                  'My attachment to KDE is primarily based on similarity of my values and those represented by this project',
                                  'What matters to me the most in participating in KDE is to try to solve complex problems',
                                  'The reason I participate in the KDE project is because of what it stands for, that is, its values',
                                  'To me, success of participation in KDE means doing better than other people',
                                  'The KDE project group has a great deal of personal meaning for me',
                                  'I really feel as if KDE\'s problems are my own',
                                  'I am keenly aware of the possible career promotion that may be brought by my participation in KDE',
                                  'I have a strong positive feeling towards KDE',
                                  'If the values of the KDE project were different, I would not be as attached to it',
                                  'What matters to me the most in participating in KDE is to enjoy fixing difficult problems',
                                  'I am keenly aware of the income goals I have for myself if I participate in KDE',
                                  'In summary, I intend to continue participating in KDE projects',
                                  'I plan to make future contributions to KDE projects',
                                  'If I could, I would like to discontinue my participation in KDE projects');

    // define answers
    $this->answer['project'][1]   = array(0 => '',
                                          5 => 'very satisfying',
                                          4 => 'satisfying',
                                          3 => 'neutral',
                                          2 => 'dissatisfying',
                                          1 => 'very dissatisfying');
    $this->answer['project'][2]   = array(0 => '',
                                          5 => 'very pleasing',
                                          4 => 'pleasing',
                                          3 => 'neutral',
                                          2 => 'displeasing',
                                          1 => 'very displeasing');
    $this->answer['project'][3]   = array(0 => '',
                                          5 => 'very contenting',
                                          4 => 'contenting',
                                          3 => 'neutral',
                                          2 => 'frustrating',
                                          1 => 'very frustrating');
    $this->answer['project'][4]   = array(0 => '',
                                          5 => 'delightful',
                                          4 => '?',
                                          3 => 'neutral',
                                          2 => '?',
                                          1 => 'terrible');

    $this->answer['contributor']  = array(0 => '',
                                          5 => 'frequently',
                                          4 => 'often',
                                          3 => 'sometimes',
                                          2 => 'rarely',
                                          1 => 'never');

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


    // show title?
    if (!$this->onlyContent) {
      $title = '<h1>' . $this->title . '</h1>';
    } else {
      $title = null;
    }

    // draw
    $buf   = '<div id="survey">
                <div id="section-0" class="section">' .
                  $title .

             '    <p class="intro">
                    Please fill out this survey to help us better understand the KDE community - it should only take 5 minutes of your time to complete.<br />
                    <i>All responses are confidential, and will only be reported in an anonymised way.</i>
                  </p>
                </div>

                <form id="survey_data" class="clearfix" action="" method="post">
                  <div id="section-1" class="section">
                    <h3>
                      Experience in Projects
                    </h3>

                    <p class="intro">
                      Add the names of the KDE projects you work in regularly, and add your responses...
                    </p>

                    <table id="projects">
                      <thead>
                        <tr>
                          <th class="padding i">Project</th>
                          <th class="padding"></th>
                          <th class="a">Satisfaction?</th>
                          <th class="a">Pleasure?</th>
                          <th class="a">Frustration?</th>
                          <th class="a">Happiness?</th>
                        </tr>
                      </thead>

                      <tbody>
                        <tr>
                          <td class="padding i">
                            <input id="project-1_name" name="project-1_name" type="text" value="" />
                          </td>
                          <td class="padding q">
                            Participating in this project was...
                          </td>

                          <td class="a">' .
                            Ui::htmlSelector('project-1_1', $this->answer['project'][1]) .
              '           </td>
                          <td class="a">' .
                            Ui::htmlSelector('project-1_2', $this->answer['project'][2]) .
              '           </td>
                          <td class="a">' .
                            Ui::htmlSelector('project-1_3', $this->answer['project'][3]) .
              '           </td>
                          <td class="a">' .
                            Ui::htmlSelector('project-1_4', $this->answer['project'][4]) .
              '          </td>
                        </tr>
                      </tbody>
                    </table>

                    <input type="button" value="Add another project" onclick="addRow($(\'projects\'));" />
                  </div>



                  <div id="section-2" class="section">
                    <h3>
                      Experience with Other Contributors
                    </h3>

                    <p class="intro">
                      Add the names (SVN/Git account, otherwise real name) of KDE contributors you work with regularly, and add your responses...
                    </p>

                    <table id="contributors">
                      <thead>
                        <tr>
                          <th class="padding i">Contributor</th>
                          <th class="a">We discuss technical issues unrelated to the project...</th>
                          <th class="a">We discuss non-technical issues unrelated to the project...</th>
                          <th class="a">The communication with him/her is rude...</th>
                          <th class="a">We meet face-to-face...</th>
                          <th class="a">The communication with him/her is considerate...</th>
                        </tr>
                      </thead>

                      <tbody>
                        <tr>
                          <td class="padding i">
                            <input id="contributor-1_name" name="contributor-1_name" type="text" value="" />
                          </td>

                          <td class="a">' .
                            Ui::htmlSelector('contributor-1_1', $this->answer['contributor']) .
              '           </td>
                          <td class="a">' .
                            Ui::htmlSelector('contributor-1_2', $this->answer['contributor']) .
              '           </td>
                          <td class="a">' .
                            Ui::htmlSelector('contributor-1_3', $this->answer['contributor']) .
              '           </td>
                          <td class="a">' .
                            Ui::htmlSelector('contributor-1_4', $this->answer['contributor']) .
              '           </td>
                          <td class="a">' .
                            Ui::htmlSelector('contributor-1_5', $this->answer['contributor']) .
              '           </td>
                        </tr>
                      </tbody>
                    </table>

                    <input type="button" value="Add another contributor" onclick="addRow($(\'contributors\'));" />
                  </div>



                  <div id="section-3" class="section">
                    <h3>
                      Your Motivation
                    </h3>

                    <p class="intro">
                      Rate your responses to the following questions using the radio button scale (strongly disagree to strongly agree)...
                    </p>

                    <table id="motivation">
                      <thead>
                        <tr>
                          <th></th>
                          <th></th>
                          <th></th>
                          <th></th>
                          <th></th>
                          <th></th>
                        </tr>
                      </thead>

                      <tbody>';

    foreach ($this->questions as $num => $question) {
      $buf  .= '        <tr>
                          <td class="q">' . $question . '</td>';

      for ($i = 1; $i <= 5; $i++) {
        $buf  .= '        <td class="a">
                            <label class="r' . $i . '" title="' . $this->answer['experience'][$i] . '">
                              <input id="motivation-' . $num .'_' . $i . '" class="observe" type="radio" name="motivation-' . $num .'" value="' . $i . '" />
                            </label>
                          </td>';
      }

      $buf  .= '        </tr>';
    }

    $buf  .= '        </tbody>
                    </table>
                  </div>

                  <input id="access_code" name="access_code" type="hidden" value="' . $this->developer->access['code'] . '" />
                  <input id="submit" type="button" value="Submit my answers" onclick="submitSurvey(event);" />
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
                $$("#motivation label")
                  .invoke("observe", "mouseover", radioMouseover)
                  .invoke("observe", "mouseout", radioMouseout);

                // setup radio button clicks + keyboard interactions
                $$("#motivation input.observe")
                  .invoke("observe", "change", radioClick)
                  .invoke("observe", "blur", radioMouseout)
                  .invoke("observe", "focus", radioMouseover);
              </script>';

    return $buf;
  }


  public function getScript() {
    return array('/js/frame/surveyui.js');
  }


  public function getStyle() {
    return array('/css/surveyui.css');
  }
}

?>