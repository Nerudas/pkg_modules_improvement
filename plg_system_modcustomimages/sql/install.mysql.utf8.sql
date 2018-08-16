CREATE TABLE IF NOT EXISTS `#__modcustomimages` (
  `id`           INT(11)          NOT NULL AUTO_INCREMENT,
  `images`       LONGTEXT         NOT NULL DEFAULT '',
  UNIQUE KEY `id` (`id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
AUTO_INCREMENT = 0;