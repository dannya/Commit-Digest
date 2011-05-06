<?php

/*-------------------------------------------------------+
| Enzyme
| Copyright 2010-2011 Danny Allen <danny@enzyme-project.org>
| http://www.enzyme-project.org/
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/


class Developer {
  public $data                  = null;

  public static $fieldSections  = array('core'            => array('account', 'nickname', 'dob', 'gender', 'motivation', 'employer', 'colour'),
                                        'geographic'      => array('continent', 'country', 'location', 'latitude', 'longitude'),
                                        'social'          => array('homepage', 'blog', 'lastfm', 'microblog_type', 'microblog_user'));

  public static $fields         = array('account'         => array('type'   => 'string',
                                                                   'public' => true),
                                        'nickname'        => array('type'   => 'string',
                                                                   'public' => true),
                                        'dob'             => array('type'   => 'date',
                                                                   'public' => true),
                                        'gender'          => array('type'   => 'enum',
                                                                   'public' => true),
                                        'motivation'      => array('type'   => 'enum',
                                                                   'public' => true),
                                        'employer'        => array('type'   => 'string',
                                                                   'public' => true),
                                        'colour'          => array('type'   => 'enum',
                                                                   'public' => true),

                                        'continent'       => array('type'   => 'enum',
                                                                   'public' => true),
                                        'country'         => array('type'   => 'string',
                                                                   'public' => true),
                                        'location'        => array('type'   => 'string',
                                                                   'public' => true),
                                        'latitude'        => array('type'   => 'float',
                                                                   'public' => true),
                                        'longitude'       => array('type'   => 'float',
                                                                   'public' => true),

                                        'homepage'        => array('type'   => 'string',
                                                                   'public' => true),
                                        'blog'            => array('type'   => 'string',
                                                                   'public' => true),
                                        'lastfm'          => array('type'   => 'string',
                                                                   'public' => true),
                                        'microblog_type'  => array('type'   => 'enum',
                                                                   'public' => true),
                                        'microblog_user'  => array('type'   => 'string',
                                                                   'public' => true));


  public function __construct($value = null, $field = 'account') {
    // load in constructor?
    if ($value) {
      $this->load($value, $field);
    }
  }


  public function load($value = null, $field = 'account') {
    if (!$value) {
      if (!isset($this->data['account'])) {
        return false;
      }

      $field = 'account';
      $value = $this->data['account'];
    }

    // load developer data
    $this->data = Db::load('developers', array($field => $value), 1);

    // stop if no developer data found
    if (!$this->data) {
      return false;
    }

    // if loading by access_code, ensure code has not expired
    if (empty($this->data['access_timeout']) || (time() > strtotime($this->data['access_timeout']))) {
      $this->data = null;
      return false;
    }

    // return successful load
    return true;
  }


  public function save() {
//    if (!isset($this->data['account'])) {
//      return false;
//    }
//
//    // serialise arrays as strings for storage
//    if (!empty($this->paths)) {
//      $this->data['paths']        = App::combineCommaList($this->paths);
//    }
//    if (!empty($this->permissions)) {
//      $this->data['permissions']  = App::combineCommaList($this->permissions);
//    }
//
//    // save changes in database
//    return Db::save('developers', array('account' => $this->data['account']), $this->data);
  }


  public static function getFieldStrings() {
    $fields  = array('account'        => _('Account'),
                     'nickname'       => _('Nickname'),
                     'dob'            => _('DOB'),
                     'gender'         => _('Gender'),
                     'motivation'     => _('Motivation'),
                     'employer'       => _('Employer'),
                     'colour'         => _('Colour'),

                     'continent'      => _('Continent'),
                     'country'        => _('Country'),
                     'location'       => _('Location'),
                     'latitude'       => _('Latitude'),
                     'longitude'      => _('Longitude'),

                     'homepage'       => _('Homepage URL'),
                     'blog'           => _('Blog URL'),
                     'lastfm'         => _('Last.fm username'),
                     'microblog_type' => _('Microblog service'),
                     'microblog_user' => _('Microblog username'));

    return $fields;
  }
}

?>