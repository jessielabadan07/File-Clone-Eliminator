-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 29, 2013 at 05:46 PM
-- Server version: 5.5.25
-- PHP Version: 5.4.4

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `file_eliminator`
--

-- --------------------------------------------------------

--
-- Table structure for table `file_list`
--

CREATE TABLE IF NOT EXISTS `file_list` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint(20) NOT NULL,
  `file_path` text NOT NULL,
  `generated_filename` text NOT NULL,
  `file_size` bigint(20) unsigned NOT NULL,
  `hash_key` text NOT NULL,
  `is_deleted` tinyint(4) NOT NULL DEFAULT '0',
  `date_modified` varchar(255) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2339 ;

-- --------------------------------------------------------

--
-- Table structure for table `file_type`
--

CREATE TABLE IF NOT EXISTS `file_type` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `extension_name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

--
-- Dumping data for table `file_type`
--

INSERT INTO `file_type` (`id`, `extension_name`) VALUES
(1, 'mp4'),
(2, 'doc'),
(3, 'gif'),
(4, 'png'),
(5, 'txt'),
(6, 'pdf'),
(7, 'mov'),
(8, 'xls');

-- --------------------------------------------------------

--
-- Table structure for table `parent_directory`
--

CREATE TABLE IF NOT EXISTS `parent_directory` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `root_directory` text NOT NULL,
  `dateadded` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
