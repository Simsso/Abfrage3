<?php

class Feed {
  public $user;
  public $events = array();
  
  function __construct($id, $since) {
    // $id is the user id for whom the feed is being created.
    // $since is a UNIX-timestamp which limits the feed content. If e.g. a list has been shared before this time it will not appear in the feed.
    $this->user = intval($id);
    
    global $con;
    
    // shared lists
    $sql = "
      SELECT `share`.`id`, `share`.`list`, `share`.`permissions`, `list`.`creator`, `share`.`time`
      FROM `share`, `list` 
      WHERE `list`.`id` = `share`.`list` AND `share`.`user` = ".$id." AND `share`.`permissions` <> 0 AND `share`.`time` >= ".$since."
      ORDER BY `share`.`time` DESC;";
    $query = mysqli_query($con, $sql);
    while ($row = mysqli_fetch_assoc($query)) {
      array_push(
        $this->events, 
        new FeedItem(
          FeedItemType::ListShared,
          intval($row['time']),
          new SharingInformation($row['id'], SimpleUser::get_by_id($row['creator']), $row['list'], $row['permissions'])
        )
      );
    }
    
    // another user has added a word to a list which is shared with you
    $sql = "
      SELECT `list`.`creator`, `word`.`user`, COUNT(`word`.`id`) AS 'amount', MIN(`word`.`time`) AS 'time', `list`.`id`
      FROM `list`, `share`, `word` 
      WHERE `share`.`user` = ".$id." AND `word`.`user` <> ".$id." AND 
        `word`.`list` = `list`.`id` AND `share`.`list` = `list`.`id` AND
        `list`.`active` <> 0 AND `share`.`permissions` <> 0 AND `word`.`status` <> 0 AND
        `word`.`time` > ".$since."
      GROUP BY `word`.`list`, `word`.`user`
      ORDER BY `word`.`time` ASC;
    ";
    $query = mysqli_query($con, $sql);

    while ($row = mysqli_fetch_assoc($query)) {
      if ($row['user'] == 0) continue;
      
      array_push(
        $this->events, 
        new FeedItem(
          FeedItemType::WordAdded,
          intval($row['time']),
          new WordsAddedFeedItem(
            $row['amount'], 
            BasicWordList::get_by_id($row['id']), 
            SimpleUser::get_by_id($row['creator']), 
            SimpleUser::get_by_id($row['user']))
        )
      );
    }
    
    // another user has added a word to your word list
    $sql = "
      SELECT `list`.`creator`, `word`.`user`, COUNT(`word`.`id`) AS 'amount', MIN(`word`.`time`) AS 'time', `list`.`id`
      FROM `list`, `word` 
      WHERE `word`.`list` = `list`.`id` AND `word`.`user` <> ".$id." AND `list`.`creator` = ".$id." AND
        `list`.`active` <> 0 AND `word`.`status` <> 0 AND
        `word`.`time` > ".$since."
      GROUP BY `word`.`list`, `word`.`user`
      ORDER BY `word`.`time` ASC;
    ";
    $query = mysqli_query($con, $sql);

    while ($row = mysqli_fetch_assoc($query)) {
      if (intval($row['user']) === 0) {
        continue;
      }
      array_push(
        $this->events, 
        new FeedItem(
          FeedItemType::WordAdded,
          intval($row['time']),
          new WordsAddedFeedItem($row['amount'], BasicWordList::get_by_id($row['id']), SimpleUser::get_by_id($row['creator']), SimpleUser::get_by_id($row['user']))
        )
      );
    }
    
    // users added
    $sql = "
      SELECT `user1`, `time`
      FROM `relationship` 
      WHERE `type` <> 0 AND `user2` = ".$id." AND `time` >= ".$since."
      ORDER BY `time` DESC;";
    $query = mysqli_query($con, $sql);
    while ($row = mysqli_fetch_assoc($query)) {
      array_push(
        $this->events, 
        new FeedItem(
          FeedItemType::UserAdded,
          intval($row['time']),
          SimpleUser::get_by_id($row['user1'])
        )
      );
    }
  }
}

class FeedItem {
  public $type;
  public $time;
  public $info;
  
  function __construct($type, $time, $info) {
    $this->type = $type;
    $this->time = $time;
    $this->info = $info;
  }
}

abstract class FeedItemType {
  const UserAdded = 0;
  const ListShared = 1;
  const WordAdded = 2;
  const WordDeleted = 3;
}

class WordsAddedFeedItem {
  public $amount;
  public $list;
  public $list_creator;
  public $user;
  
  function __construct($amount, BasicWordList $list, SimpleUser $list_creator, SimpleUser $user) {
    $this->amount = intval($amount);
    $this->list = $list;
    $this->list_creator = $list_creator;
    $this->user = $user;
  }
}

?>