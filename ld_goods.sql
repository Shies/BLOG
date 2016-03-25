-- phpMyAdmin SQL Dump
-- version 4.0.5-rc2
-- http://www.phpmyadmin.net
--
-- 主机: 127.0.0.1
-- 生成日期: 2014 年 02 月 24 日 17:35
-- 服务器版本: 5.5.33
-- PHP 版本: 5.4.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 数据库: `openfire`
--

-- --------------------------------------------------------

--
-- 表的结构 `ld_goods`
--

CREATE TABLE IF NOT EXISTS `ld_goods` (
  `goods_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `cat_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `goods_sn` varchar(60) NOT NULL DEFAULT '',
  `goods_name` varchar(120) NOT NULL DEFAULT '',
  `spec_qty` tinyint(1) NOT NULL DEFAULT '0',
  `spec_name_1` varchar(30) NOT NULL DEFAULT '',
  `spec_name_2` varchar(30) NOT NULL DEFAULT '',
  `default_spec` smallint(5) NOT NULL DEFAULT '0',
  `click_count` int(10) unsigned NOT NULL DEFAULT '0',
  `brand_name` varchar(255) NOT NULL DEFAULT '',
  `goods_number` smallint(5) unsigned NOT NULL DEFAULT '0',
  `market_price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `shop_price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `warn_number` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `goods_desc` text NOT NULL,
  `goods_thumb` varchar(255) NOT NULL DEFAULT '',
  `goods_img` varchar(255) NOT NULL DEFAULT '',
  `original_img` varchar(255) NOT NULL DEFAULT '',
  `is_on_sale` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `add_time` int(10) unsigned NOT NULL DEFAULT '0',
  `sort_order` smallint(4) unsigned NOT NULL DEFAULT '100',
  `last_update` int(10) unsigned NOT NULL DEFAULT '0',
  `is_recommend` tinyint(1) unsigned DEFAULT NULL,
  PRIMARY KEY (`goods_id`),
  KEY `goods_sn` (`goods_sn`),
  KEY `cat_id` (`cat_id`),
  KEY `last_update` (`last_update`),
  KEY `brand_id` (`brand_name`),
  KEY `goods_number` (`goods_number`),
  KEY `sort_order` (`sort_order`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

--
-- 转存表中的数据 `ld_goods`
--

INSERT INTO `ld_goods` (`goods_id`, `user_id`, `cat_id`, `goods_sn`, `goods_name`, `spec_qty`, `spec_name_1`, `spec_name_2`, `default_spec`, `click_count`, `brand_name`, `goods_number`, `market_price`, `shop_price`, `warn_number`, `goods_desc`, `goods_thumb`, `goods_img`, `original_img`, `is_on_sale`, `add_time`, `sort_order`, `last_update`, `is_recommend`) VALUES
(8, 0, 0, '22222', '222222', 0, '', '', 0, 0, '', 22222, '0.00', '22222.00', 1, '1111111112222', '', '', '', 1, 1393232243, 100, 0, NULL),
(9, 0, 0, '111', '111', 0, '', '', 0, 0, '', 111, '0.00', '1111.00', 1, '1111', '', '', '', 1, 1393232246, 100, 0, NULL);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
