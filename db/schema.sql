CREATE TABLE `im_private_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_from_id` int(11) DEFAULT NULL,
  `user_to_id` int(11) DEFAULT NULL,
  `content` text,
  `is_notified` tinyint(1) DEFAULT NULL,
  `is_viewed` tinyint(1) DEFAULT NULL,
  `create_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `titan_im_public_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_from_id` int(11) DEFAULT NULL,
  `user_to_id` varchar(45) DEFAULT NULL,
  `content` text,
  `create_time` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
