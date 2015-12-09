<?php

class BasicWordList {
  public $id;
  public $name;
  public $creator;
  public $comment;
  public $language1;
  public $language2;
  public $creation_time;
  public $words;
  public $sharings;

  public function __construct($id, $name, $creator, $comment, $language1, $language2, $creation_time) {
    $this->id = intval($id);
    $this->name = htmlspecialchars_decode($name);

    // creator can be SimpleUser or Integer
    if ($creator instanceof SimpleUser)
      $this->creator = $creator;
    else 
      $this->creator = intval($creator);

    $this->comment = htmlspecialchars_decode($comment);
    $this->language1 = htmlspecialchars_decode($language1);
    $this->language2 = htmlspecialchars_decode($language2);
    $this->creation_time = intval($creation_time);

    if ($this->language1 == null) $this->language1 = "First language";
    if ($this->language2 == null) $this->language2 = "Second language";
  }

  public function get_by_id($id) {
    global $con;

    $sql = "SELECT * FROM `list` WHERE `id` = ".$id.";";
    $query = mysqli_query($con, $sql);
    while ($row = mysqli_fetch_assoc($query)) {
      return new BasicWordList($id, $row['name'], $row['creator'], $row['comment'], $row['language1'], $row['language2'], $row['creation_time']);
    }
    return null;
  }


  // load words
  //
  // @param bool loadAnswers: load Word objects with answer array filled
  // @param unsigned int user_id: id of the user
  // @param string order: "ASC" or "DESC"
  public function load_words($loadAnswers, $user_id, $order) {
    global $con;

    $sql = "SELECT * FROM `word` WHERE `list` = ".$this->id." AND `status` = 1 ORDER BY `word`.`id` ".$order.";";
    $query = mysqli_query($con, $sql);
    $this->words = array();
    while ($row = mysqli_fetch_assoc($query)) {
      $word = new Word($row['id'], $row['list'], $row['language1'], $row['language2'], $row['comment']);
      if ($loadAnswers) {
        $word->load_answers($user_id);
      }
      array_push($this->words, $word);
    }
  }
  

  // get editing permissions for user
  //
  // @param unsigned int id: id of the user
  //
  // @return bool: 
  //  - true if the user given via $id has permissions to edit the list
  //  - false if the user has no permissions to edit but permissions to view or no permissions at all
  public function get_editing_permissions_for_user($id) {
    global $con;
    
    $sql = "SELECT COUNT(`id`) AS 'count' FROM `share` WHERE `list` = ".$this->id." AND `user` = ".$id." AND `permissions` = 2;";
    $query = mysqli_query($con, $sql);
    $count = mysqli_fetch_object($query)->count;
    
    return ($count == 0);
  }


  // get sharing information of list
  //
  // @param unsigned int user_id: id of the user
  // @param unsigned int word_list_id: id of the word list
  //
  // @return SharingInformation[]: sharing information of the list
  static function get_sharing_info_of_list($user_id, $word_list_id) {
    global $con;
    $sql = "
    SELECT `share`.`permissions`, `share`.`id` AS 'share_id', `share`.`list`, `user`.`id` AS 'user_id', `user`.`firstname`, `user`.`lastname`, `user`.`email`
    FROM `share`, `list`, `user`
    WHERE `share`.`list` = `list`.`id` AND `share`.`user` = `user`.`id` AND `list`.`id` = ".$word_list_id." AND `list`.`creator` = ".$user_id." AND `list`.`active` = 1 AND `share`.`permissions` <> 0
        ORDER BY `user`.`firstname` ASC, `user`.`lastname` ASC;";
    $query = mysqli_query($con, $sql);
    $result = array();
    while ($row = mysqli_fetch_assoc($query)) {
      array_push($result, new SharingInformation($row['share_id'], new SimpleUser($row['user_id'], $row['firstname'], $row['lastname'], $row['email']), $row['list'], $row['permissions']));
    }
    return $result;
  }

  // get words of list
  //
  // @param unsigned int list_id: the id of the word list
  //
  // @return Word[]: array of the lists words
  static function get_words_of_list($list_id) {
    global $con;
    $sql = "SELECT `id`, `list`, `language1`, `language2`, `comment` FROM `word` WHERE `list` = ".$list_id." AND `status` = 1 ORDER BY `id` DESC";
    $query = mysqli_query($con, $sql);
    $output = array();
    while ($row = mysqli_fetch_assoc($query)) {
      array_push($output, new Word($row['id'], $row['list'], $row['language1'], $row['language2'], $row['comment']));
    }
    return $output;
  }


  // load sharing information
  //
  // update the object's sharing attribute to the appropriate value
  //
  // @param unsigned int user_id: id of the user
  public function load_sharing_information($user_id) {
    $this->sharings = self::get_sharing_info_of_list($user_id, $this->id);
  }
}


class WordList extends BasicWordList {
  // additionally stores words of the list and the list's labels
  public $words;
  public $labels;

  public function __construct($id, $name, $creator, $comment, $language1, $language2, $creation_time, $words) {
    parent::__construct($id, $name, $creator, $comment, $language1, $language2, $creation_time);
    $this->words = $words;
  }

  static function get_labels_of_list($user, $id) {
    global $con;

    $sql = "
		SELECT `label`.`id`, `label`.`user`, `label`.`name`, `label`.`parent`
		FROM `label`, `label_attachment`, `list`
		WHERE `label_attachment`.`label` = `label`.`id` AND `label`.`user` = ".$user." AND
		`label`.`active` = 1 AND `label_attachment`.`active` = 1 AND
		`label_attachment`.`list` = ".$id."
		GROUP BY `label`.`id`;";
    $query = mysqli_query($con, $sql);
    $output = array();
    while ($row = mysqli_fetch_assoc($query)) {
      array_push($output, new Label($row['id'], $row['name'], $row['user'], $row['parent'], 1));
    }
    return $output;
  }

  function set_labels($labels) {
    $this->labels = $labels;
  }
}


class SharingInformation {
  public $id;
  public $user;
  public $permissions;
  public $list;

  public function __construct($id, SimpleUser $user, $list, $permissions) {
    $this->id = intval($id);
    $this->user = $user;
    $this->permissions = intval($permissions);
    $this->list = BasicWordList::get_by_id($list);
  }
}

?>