# Abfrage3

## Data base
- phpMyAdmin SQL Dump
- version 3.4.10.1deb1
- http://www.phpmyadmin.net

- Host: localhost
- Generation Time: Aug 23, 2015 at 04:56 PM
- Server version: 5.5.44
- PHP Version: 5.3.10-1ubuntu3.19

```sql 
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00"; 
```

- Database: `db142619x2289845`


### Table structure for table `answer`

```sql 
CREATE TABLE `answer` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(10) unsigned NOT NULL,
  `word` int(10) unsigned NOT NULL,
  `correct` tinyint(11) unsigned NOT NULL,
  `time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='Query answers'; 
```


### Table structure for table `favorite_list`

```sql 
CREATE TABLE `favorite_list` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(10) unsigned NOT NULL,
  `list` int(10) unsigned NOT NULL,
  `active` tinyint(3) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='List favorites of users'; 
```


### Table structure for table `label`

```sql 
CREATE TABLE `label` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(10) unsigned NOT NULL,
  `name` text NOT NULL,
  `parent` int(11) DEFAULT NULL,
  `active` tinyint(3) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='Labels of lists from users'; 
```


### Table structure for table `label_attachment`

```sql 
CREATE TABLE `label_attachment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `list` int(10) unsigned NOT NULL,
  `label` int(10) unsigned NOT NULL,
  `active` tinyint(3) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='Connects labels and lists'; 
```


### Table structure for table `list`

```sql 
CREATE TABLE `list` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `creator` int(10) unsigned NOT NULL,
  `comment` text NOT NULL,
  `language1` text NOT NULL,
  `language2` text NOT NULL,
  `creation_time` int(10) unsigned NOT NULL,
  `active` tinyint(3) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='Word lists'; 
```


### Table structure for table `login`

```sql 
CREATE TABLE `login` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(11) unsigned NOT NULL,
  `time` int(11) unsigned NOT NULL,
  `ip` text NOT NULL,
  `stay_logged_in_hash` text,
  `stay_logged_in_salt` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='Login information'; 
```


### Table structure for table `relationship`

```sql 
CREATE TABLE `relationship` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user1` int(10) unsigned NOT NULL,
  `user2` int(10) unsigned NOT NULL,
  `time` int(10) unsigned NOT NULL,
  `type` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='Relationships between users'; 
```


### Table structure for table `share`

```sql 
CREATE TABLE `share` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(10) unsigned NOT NULL,
  `list` int(10) unsigned NOT NULL,
  `permissions` int(10) unsigned NOT NULL DEFAULT '0',
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='Sharings of word lists'; 
```


### Table structure for table `user`

```sql 
CREATE TABLE `user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `firstname` text NOT NULL,
  `lastname` text NOT NULL,
  `email` text NOT NULL,
  `password` text NOT NULL,
  `salt` int(10) unsigned NOT NULL,
  `reg_time` int(11) unsigned NOT NULL,
  `email_confirmed` tinyint(1) NOT NULL DEFAULT '0',
  `email_confirmation_key` text NOT NULL,
  `active` tinyint(3) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='Registered users';
```


### Table structure for table `word`

```sql 
CREATE TABLE `word` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `list` int(10) unsigned NOT NULL,
  `language1` text NOT NULL,
  `language2` text NOT NULL,
  `status` int(10) unsigned NOT NULL DEFAULT '1',
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='Words of lists'; 
```