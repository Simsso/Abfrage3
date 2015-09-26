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

  public function __construct($id, $name, $creator, $comment, $language1, $language2, $creation_time) {
    $this->id = intval($id);
    $this->name = $name;

    // creator can be SimpleUser or Integer
    if ($creator instanceof SimpleUser)
      $this->creator = $creator;
    else 
      $this->creator = intval($creator);

    $this->comment = $comment;
    $this->language1 = $language1;
    $this->language2 = $language2;
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
      $word = new Word($row['id'], $row['list'], $row['language1'], $row['language2']);
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