-- phpMyAdmin SQL Dump
-- version 3.2.0.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 06, 2010 at 04:04 PM
-- Server version: 5.1.37
-- PHP Version: 5.3.0

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `API`
--

-- --------------------------------------------------------

--
-- Table structure for table `CACHE`
--

CREATE TABLE IF NOT EXISTS `CACHE` (
  `Key` text NOT NULL,
  `Value` text NOT NULL,
  `ExpireTime` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `CACHE`
--


-- --------------------------------------------------------

--
-- Table structure for table `Developers`
--

CREATE TABLE IF NOT EXISTS `Developers` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `DeveloperID` text NOT NULL,
  `APIKey` text NOT NULL,
  `UserActions` tinyint(1) NOT NULL,
  `IsAdmin` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `Developers`
--


-- --------------------------------------------------------

--
-- Table structure for table `LOG`
--

CREATE TABLE IF NOT EXISTS `LOG` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `IP` text NOT NULL,
  `TimeStamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Action` text NOT NULL,
  `ERROR` text NOT NULL,
  `APIKey` text NOT NULL,
  `DeveloperID` text NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `LOG`
--

