<?php

/*-------------------------------------------------------+
 | KDE Commit-Digest
 | Copyright 2010-2013 Danny Allen <danny@commit-digest.org>
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


class Chart {
  private $id      = null;
  private $title   = null;

  private $size    = array('width'  => 400,
                           'height' => 180);

  private $options = array('colorScheme' => 'blue',
                           'axis'        => array('y' => array('hide' => true),
                                                  'x' => array('hide' => true)),
                           'background'  => array('hide'   => true),
                           'legend'      => array('position' => array('top'   => '0px',
                                                                      'left'  => '200px')),
                           'stroke'      => array('shadow' => false),
                           'padding'     => array('left'   => 0,
                                                  'right'  => 200,
                                                  'top'    => 0,
                                                  'bottom' => 0));

  private $data    = array();


  public function __construct($id, $values, $title = null, $size = null, $options = null) {
    $this->id     = $id;
    $this->title  = $title;

    // define keys
    $keys = array('unknown'     => _('Unknown'),
                  'volunteer'   => _('Volunteer'),
                  'commercial'  => _('Commercial'),

                  'm'           => _('Male'),
                  'male'        => _('Male'),
                  'f'           => _('Female'),
                  'female'      => _('Female'),

                  '-18'         => _('Under 18'),
                  '18-25'       => _('18 to 25'),
                  '25-35'       => _('25 to 35'),
                  '35-45'       => _('35 to 45'),
                  '45-55'       => _('45 to 55'),
                  '55-65'       => _('55 to 65'),
                  '65-75'       => _('65 to 75'),
                  '75-85'       => _('75 to 85'),
                  '85-'         => _('Over 85'));

    // process data
    foreach ($values as $key => $value) {
      if (isset($keys[$key])) {
        $this->data[] = array(
          'label' => $keys[$key],
          'data'  => (float)$value
        );

      } else {
        // set value as normal (used in bar charts, etc)
        $this->data[$key] = $value;
      }
    }

    // set size
    if (is_array($size)) {
      $this->size = array_merge($size, $this->size);
    }

    // set options
    if (is_array($options)) {
      $this->options = array_merge($options, $this->options);
    }

    if (!isset($this->options['barwidth'])) {
      $this->options['barwidth'] = 140;
    }
  }


  public function drawPie() {
    $buf = '<div id="' . $this->id . '-container">
              <h3>' . $this->title . '</h3>
              <div id="' . $this->id . '" style="width: ' . $this->size['width'] . 'px; height: ' . $this->size['height'] . 'px;"></div>
            </div>

            <script>
                if (window.dataset === undefined) {
                    window.dataset = [];
                }
                if (window.datasetElement === undefined) {
                    window.datasetElement = [];
                }

                window.dataset.push(' . json_encode($this->data) . ');
                window.datasetElement.push("#' . $this->id . '");
            </script>';

    return $buf;
  }


  public function drawBar($drawVisuals) {
    // set header?
    if (isset($this->options['header']) && is_array($this->options['header'])) {
      $header =  '<thead>
                    <tr>';

      foreach ($this->options['header'] as $theHeader) {
        $header .= '<td>' . $theHeader . '</td>' . "\n";
      }

      $header .= '  </tr>
                  </thead>';

    } else {
      $header = null;
    }

    // sort data
    arsort($this->data, SORT_NUMERIC);

    // get largest value for percentage calculation (bar width)
    $largestValue = reset($this->data);


    // draw chart
    $buf = '<table id="' . $this->id . '" class="chart-bar">' .
              $header .
           '  <tbody>';

    foreach ($this->data as $label => $value) {
      // append percentage symbol?
      if (!empty($this->options['percent'])) {
        $value .= '%';
      }

      // draw visuals?
      if ($drawVisuals) {
        $bar1 = '<div style="width:' . $this->getBarWidth($value, $largestValue) . '%;">&nbsp;</div>';
      } else {
        $bar1 = '';
      }

      $buf .=  '<tr>
                  <td class="label">' . $label . '</td>
                  <td class="value">
                    <div>
                      <span>' . $value . '</span>' . $bar1 .
               '    </div>
                  </td>
                </tr>';
    }

    $buf .=  '  </tbody>
              </table>';

    return $buf;
  }


  public function drawTwinBar($drawVisuals) {
    // set header?
    if (isset($this->options['header']) && is_array($this->options['header'])) {
      $header =  '<thead>
                    <tr>';

      foreach ($this->options['header'] as $theHeader) {
        $header .= '<td>' . $theHeader . '</td>' . "\n";
      }

      $header .= '  </tr>
                  </thead>';

    } else {
      $header = null;
    }


    // get largest values from each column for percentage calculation (bar width)
    $largestValue = array(0, 0);

    foreach ($this->data as $value) {
      if ($value[0] > $largestValue[0]) {
        $largestValue[0] = $value[0];
      }

      if ($value[1] > $largestValue[1]) {
        $largestValue[1] = $value[1];
      }
    }


    // draw chart
    $buf = '<table id="' . $this->id . '" class="chart-bar">' .
              $header .
           '  <tbody>';

    foreach ($this->data as $label => $value) {
      // append percentage symbol?
      if (!empty($this->options['percent'])) {
        $value .= '%';
      }

      // draw visuals?
      if ($drawVisuals) {
        $bar1 = '<div style="width:' . $this->getBarWidth($value[0], $largestValue[0]) . '%;">&nbsp;</div>';
        $bar2 = '<div style="width:' . $this->getBarWidth($value[1], $largestValue[1]) . '%;">&nbsp;</div>';
      } else {
        $bar1 = '';
        $bar2 = '';
      }

      $buf .=  '<tr>
                  <td class="valueTwinStart">
                    <div>
                      <span>' . $value[0] . '</span>' . $bar1 .
               '    </div>
                  </td>
                  <td class="labelTwin">' . $label . '</td>
                  <td class="valueTwinEnd">
                    <div>
                      <span>' . $value[1] . '</span>' . $bar2 .
               '    </div>
                  </td>
                </tr>';
    }

    $buf .=  '  </tbody>
              </table>';

    return $buf;
  }


  private function getBarWidth($value, $largestValue) {
    $largestValue = (int)$largestValue;

    if ($largestValue != 0) {
      // return floor(((int)$value / $largestValue) * $barWidth);
      return floor(((int)$value / $largestValue) * 100);
    } else {
      return 0;
    }
  }
}

?>