<?php

class Answer {
  public $id;
  public $user;
  public $word;
  public $correct;
  public $time;

  function __construct($id, $user, $word, $correct, $time) {
    $this->id = intval($id);
    $this->user = intval($user);
    $this->word = intval($word);
    $this->correct = intval($correct);
    $this->time = intval($time);
  }

  static function get_by_id($id) {
    global $con;
    $sql = "SELECT * FROM `answer` WHERE `id` = ".$id.";";
    $query = mysqli_query($con, $sql);
    while ($row = mysqli_fetch_assoc($query)) {
      return new Answer($row['id'], $row['user'], $row['word'], $row['correct'], $row['time']);
    }
  }
}

?>