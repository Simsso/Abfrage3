<?php

class LabelAttachment {
  public $id;
  public $list;
  public $label;
  public $active;

  function __construct($id, $list, $label, $active) {
    $this->id = intval($id);
    $this->list = intval($list);
    $this->label = intval($label);
    $this->active = intval($active);
  }
}


class Label {
  public $id;
  public $name;
  public $user;
  public $parent_label;
  public $active;

  public function __construct($id, $name, $user, $parent_label, $active) {
    $this->id = intval($id);
    $this->name = $name;
    $this->user = intval($user);
    $this->parent_label = intval($parent_label);
    $this->active = intval($active);
  }

  // get label by id
  //
  // second constructor (by id)
  // 
  // @param unsigned int id: id of the label
  //
  // @return Label | null: the corresponding label object or null if the id is invalid
  public function get_by_id($id) {
    global $con;

    $sql = "SELECT * FROM `label` WHERE `id` = ".$id.";";
    $query = mysqli_query($con, $sql);
    while ($row = mysqli_fetch_assoc($query)) {
      return new Label($id, $row['name'], $row['user'], $row['parent_label'], $row['active']);
    }
    return NULL;
  }
}

?>