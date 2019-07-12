-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 19, 2019 at 08:53 PM
-- Server version: 5.7.26-0ubuntu0.16.04.1
-- PHP Version: 7.0.33-0ubuntu0.16.04.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `Crypto360`
--

-- --------------------------------------------------------

--
-- Table structure for table `Api_Keys`
--

CREATE TABLE `Api_Keys` (
  `merchant_id` varchar(32) NOT NULL,
  `api_key` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- --------------------------------------------------------

--
-- Table structure for table `merchant_bitcoin_detail`
--

CREATE TABLE `merchant_bitcoin_detail` (
  `merchant_id` varchar(32) NOT NULL,
  `Bitcoin` tinyint(1) NOT NULL,
  `xpubkey` text,
  `indexValue` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- --------------------------------------------------------

--
-- Table structure for table `merchant_profile`
--

CREATE TABLE `merchant_profile` (
  `merchant_id` varchar(32) NOT NULL,
  `firstName` text NOT NULL,
  `lastName` text NOT NULL,
  `email` text NOT NULL,
  `password` varchar(32) NOT NULL,
  `organizationName` text,
  `verified` tinyint(4) NOT NULL DEFAULT '0',
  `authorization_token` varchar(32) NOT NULL,
  `no_of_api` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- --------------------------------------------------------

--
-- Table structure for table `Payment_Details`
--

CREATE TABLE `Payment_Details` (
  `payment_id` varchar(32) NOT NULL,
  `merchant_id` varchar(32) NOT NULL,
  `address` text NOT NULL,
  `tx_timestamp` text NOT NULL,
  `invoice_id` text NOT NULL,
  `amount` float NOT NULL,
  `tx_id` text NOT NULL,
  `Unused_Addresses` text NOT NULL,
  `confirmations` int(11) NOT NULL,
  `Date` date DEFAULT NULL,
  `Coin` text NOT NULL,
  `user_email` text NOT NULL,
  `callback_url` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Api_Keys`
--
ALTER TABLE `Api_Keys`
  ADD KEY `merchant_id` (`merchant_id`);

--
-- Indexes for table `merchant_bitcoin_detail`
--
ALTER TABLE `merchant_bitcoin_detail`
  ADD KEY `merchant_id` (`merchant_id`);

--
-- Indexes for table `merchant_profile`
--
ALTER TABLE `merchant_profile`
  ADD PRIMARY KEY (`merchant_id`);

--
-- Indexes for table `Payment_Details`
--
ALTER TABLE `Payment_Details`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `merchant_id` (`merchant_id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
