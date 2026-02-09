-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Feb 06, 2026 at 07:07 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hygeadb`
--

-- --------------------------------------------------------

--
-- Table structure for table `branch`
--

CREATE TABLE `branch` (
  `branch_id` int(11) NOT NULL,
  `branch_name` varchar(100) NOT NULL,
  `branch_address` varchar(255) DEFAULT NULL,
  `branch_status` varchar(20) DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branch`
--

INSERT INTO `branch` (`branch_id`, `branch_name`, `branch_address`, `branch_status`) VALUES
(1, 'Branch 1', 'Taytay (1)', 'Active'),
(2, 'Branch 2', 'Pasig City', 'Active'),
(3, 'Branch 3', 'Taytay (2)', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`category_id`, `category_name`) VALUES
(1, 'Analgesics / Pain Relievers'),
(2, 'Antipyretics'),
(3, 'Antibiotics'),
(4, 'Antivirals'),
(5, 'Antifungals'),
(6, 'Antihistamines'),
(7, 'Cough & Cold Preparations'),
(8, 'Bronchodilators'),
(9, 'Antacids'),
(10, 'Anti-diarrheals'),
(11, 'Laxatives'),
(12, 'Antihypertensives'),
(13, 'Diuretics'),
(14, 'Cholesterol-Lowering Drugs'),
(15, 'Antidiabetics'),
(16, 'Thyroid Medications'),
(17, 'Vitamins'),
(18, 'Minerals'),
(19, 'Herbal Supplements'),
(20, 'Topical Steroids'),
(21, 'Antiseptics'),
(22, 'Skin Treatments'),
(23, 'Antidepressants'),
(24, 'Anti-anxiety Medications'),
(25, 'Contraceptives'),
(26, 'Prenatal Vitamins'),
(27, 'Pediatric Medicines'),
(28, 'Anesthetics'),
(29, 'Muscle Relaxants'),
(30, 'Ophthalmic Preparations'),
(31, 'Ear & Nasal Preparations'),
(32, 'Medical Supplies'),
(33, 'Food Supplements'),
(34, 'Vitamins (Tablets / Capsules)'),
(35, 'Ointments / Creams / Drops');

-- --------------------------------------------------------

--
-- Table structure for table `invoice`
--

CREATE TABLE `invoice` (
  `invoice_id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `invoice_date` datetime NOT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_items`
--

CREATE TABLE `invoice_items` (
  `invoice_item_id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `batch_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medicine`
--

CREATE TABLE `medicine` (
  `medicine_id` int(11) NOT NULL,
  `generic_name` varchar(100) NOT NULL,
  `brand_name` varchar(100) DEFAULT NULL,
  `dosage_form` varchar(50) DEFAULT NULL,
  `strength` varchar(50) DEFAULT NULL,
  `barcode` varchar(50) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medicine`
--

INSERT INTO `medicine` (`medicine_id`, `generic_name`, `brand_name`, `dosage_form`, `strength`, `barcode`, `category_id`) VALUES
(1, 'Amoxicillin', 'Generic', 'Capsule', '250mg', 'PH-AMOX-250', 3),
(2, 'Amoxicillin', 'Generic', 'Capsule', '500mg', 'PH-AMOX-500', 3),
(3, 'Azithromycin', 'Generic', 'Tablet', '500mg', 'PH-AZI-500', 3),
(4, 'Cefalexin', 'Generic', 'Capsule', '250mg', 'PH-CEFAL-250', 3),
(5, 'Cefalexin', 'Generic', 'Capsule', '500mg', 'PH-CEFAL-500', 3),
(6, 'Cefixime', 'Generic', 'Tablet', '200mg', 'PH-CEFIX-200', 3),
(7, 'Cefuroxime', 'Generic', 'Tablet', '500mg', 'PH-CEFURO-500', 3),
(8, 'Clindamycin', 'Generic', 'Capsule', '300mg', 'PH-CLINDA-300', 3),
(9, 'Cloxacillin', 'Generic', 'Capsule', '500mg', 'PH-CLOX-500', 3),
(10, 'Co-amoxiclav', 'Generic', 'Tablet', '625mg', 'PH-COAMOX-625', 3),
(11, 'Cotrimoxazole', 'Generic', 'Tablet', '960mg', 'PH-COTRI-960', 3),
(12, 'Doxycycline', 'Generic', 'Capsule', '100mg', 'PH-DOXY-100', 3),
(13, 'Erythromycin', 'Generic', 'Tablet', '500mg', 'PH-ERY-500', 3),
(14, 'Levofloxacin', 'Generic', 'Capsule', '500mg', 'PH-LEVO-500', 3),
(15, 'Rifampicin', 'Generic', 'Capsule', '450mg', 'PH-RIF-450', 3),
(16, 'Paracetamol', 'Generic', 'Tablet', '500mg', 'PH-PARA-500', 1),
(17, 'Ibuprofen', 'Generic', 'Tablet', '400mg', 'PH-IBU-400', 1),
(18, 'Mefenamic Acid', 'Generic', 'Capsule', '250mg', 'PH-MEF-250', 1),
(19, 'Mefenamic Acid', 'Generic', 'Capsule', '500mg', 'PH-MEF-500', 1),
(20, 'Celecoxib', 'Generic', 'Capsule', '200mg', 'PH-CELECOX-200', 1),
(21, 'Meloxicam', 'Generic', 'Tablet', '15mg', 'PH-MELOX-15', 1),
(22, 'Diclofenac', 'Generic', 'Tablet', '50mg', 'PH-DICLO-50', 1),
(23, 'Diclofenac', 'Generic', 'Tablet', '100mg', 'PH-DICLO-100', 1),
(24, 'Piroxicam', 'Generic', 'Capsule', '20mg', 'PH-PIROX-20', 1),
(25, 'Tranexamic Acid', 'Generic', 'Capsule', '500mg', 'PH-TRANEX-500', 1),
(26, 'Aspirin', 'Generic', 'Tablet', '80mg', 'PH-ASP-80', 1),
(27, 'Amlodipine', 'Generic', 'Tablet', '5mg', 'PH-AMLO-5', 11),
(28, 'Amlodipine', 'Generic', 'Tablet', '10mg', 'PH-AMLO-10', 11),
(29, 'Losartan', 'Generic', 'Tablet', '50mg', 'PH-LOS-50', 11),
(30, 'Losartan', 'Generic', 'Tablet', '100mg', 'PH-LOS-100', 11),
(31, 'Captopril', 'Generic', 'Tablet', '25mg', 'PH-CAP-25', 11),
(32, 'Carvedilol', 'Generic', 'Tablet', '6.25mg', 'PH-CARV-6', 11),
(33, 'Carvedilol', 'Generic', 'Tablet', '25mg', 'PH-CARV-25', 11),
(34, 'Propranolol', 'Generic', 'Tablet', '10mg', 'PH-PROP-10', 11),
(35, 'Propranolol', 'Generic', 'Tablet', '40mg', 'PH-PROP-40', 11),
(36, 'Metoprolol', 'Generic', 'Tablet', '50mg', 'PH-METO-50', 11),
(37, 'Metoprolol', 'Generic', 'Tablet', '100mg', 'PH-METO-100', 11),
(38, 'Isosorbide Mononitrate', 'Generic', 'Tablet', '30mg', 'PH-ISO-30', 11),
(39, 'Metformin', 'Generic', 'Tablet', '500mg', 'PH-MET-500', 15),
(40, 'Glibenclamide', 'Generic', 'Tablet', '5mg', 'PH-GLIB-5', 15),
(41, 'Gliclazide', 'Generic', 'Tablet', '30mg', 'PH-GLIC-30', 15),
(42, 'Gliclazide', 'Generic', 'Tablet', '80mg', 'PH-GLIC-80', 15),
(43, 'Glimepiride', 'Generic', 'Tablet', '2mg', 'PH-GLIM-2', 15),
(44, 'Ambroxol', 'Generic', 'Tablet', '30mg', 'PH-AMB-30', 7),
(45, 'Ambroxol', 'Generic', 'Capsule', '75mg', 'PH-AMB-75', 7),
(46, 'Carbocisteine', 'Generic', 'Tablet', '500mg', 'PH-CARB-500', 7),
(47, 'Lagundi', 'Generic', 'Tablet', '600mg', 'PH-LAG-600', 7),
(48, 'Salbutamol', 'Generic', 'Tablet', '2mg', 'PH-SAL-2', 7),
(49, 'Salbutamol', 'Generic', 'Tablet', '4mg', 'PH-SAL-4', 7),
(50, 'Loratadine', 'Generic', 'Tablet', '10mg', 'PH-LORA-10', 7),
(51, 'Cetirizine', 'Generic', 'Tablet', '10mg', 'PH-CET-10', 7),
(52, 'Diphenhydramine', 'Generic', 'Capsule', '50mg', 'PH-DIPH-50', 7),
(53, 'Guaifenesin', 'Generic', 'Capsule', '100mg', 'PH-GUAI-100', 7),
(54, 'Ascorbic Acid', 'Generic', 'Tablet', '500mg', 'PH-VITC-500', 17),
(55, 'Folic Acid', 'Generic', 'Capsule', '5mg', 'PH-FOLIC-5', 17),
(56, 'Calcium Carbonate', 'Generic', 'Tablet', '500mg', 'PH-CAL-500', 17),
(57, 'Calcium Lactate', 'Generic', 'Tablet', '325mg', 'PH-CAL-LAC', 17),
(58, 'Multivitamins', 'Generic', 'Tablet', 'Adult', 'PH-MULTI-ADULT', 17),
(59, 'Zinc Sulfate', 'Generic', 'Tablet', 'Standard', 'PH-ZINC-TAB', 17),
(60, 'Omeprazole', 'Generic', 'Capsule', '20mg', 'PH-OME-20', 7),
(61, 'Omeprazole', 'Generic', 'Capsule', '40mg', 'PH-OME-40', 7),
(62, 'Ranitidine', 'Generic', 'Tablet', '300mg', 'PH-RANI-300', 7),
(63, 'Domperidone', 'Generic', 'Tablet', '10mg', 'PH-DOMP-10', 7),
(64, 'Metoclopramide', 'Generic', 'Tablet', '10mg', 'PH-METOCL-10', 7),
(65, 'Loperamide', 'Generic', 'Capsule', '2mg', 'PH-LOP-2', 7),
(66, 'Acetylcysteine', 'Generic', 'Powder Sachet', '600mg', 'PH-ACET-600', 7),
(67, 'Aluminum Magnesium', 'Generic', 'Tablet', '—', 'PH-ALMG-TAB', 9),
(68, 'Betahistine', 'Generic', 'Tablet', '16mg', 'PH-BETA-16', 12),
(69, 'Bisacodyl', 'Generic', 'Tablet', '5mg', 'PH-BIS-5', 11),
(70, 'Cinnarizine', 'Generic', 'Tablet', '25mg', 'PH-CIN-25', 6),
(71, 'Clopidogrel', 'Generic', 'Tablet', '75mg', 'PH-CLOP-75', 12),
(72, 'Colchicine', 'Generic', 'Tablet', '500mcg', 'PH-COL-500', 1),
(73, 'Dexamethasone', 'Generic', 'Tablet', '500mcg', 'PH-DEX-500', 20),
(74, 'Dicycloverine', 'Generic', 'Tablet', '10mg', 'PH-DICY-10', 9),
(75, 'Etoricoxib', 'Generic', 'Tablet', '90mg', 'PH-ETO-90', 1),
(76, 'Etoricoxib', 'Generic', 'Tablet', '120mg', 'PH-ETO-120', 1),
(77, 'Felodipine', 'Generic', 'Tablet', '5mg', 'PH-FELO-5', 12),
(78, 'Furosemide', 'Generic', 'Tablet', '20mg', 'PH-FURO-20', 13),
(79, 'Furosemide', 'Generic', 'Tablet', '40mg', 'PH-FURO-40', 13),
(80, 'Hyoscine', 'Generic', 'Tablet', '10mg', 'PH-HYOS-10', 9),
(81, 'Ibuprofen + Paracetamol', 'Generic', 'Capsule', '—', 'PH-IBUPAR-CAP', 1),
(82, 'Isosorbide Dinitrate', 'Generic', 'Tablet', '5mg', 'PH-ISOD-5', 12),
(83, 'Levothyroxine', 'Generic', 'Tablet', '50mg', 'PH-LEVO-50', 16),
(84, 'Losartan + Hydrochlorothiazide', 'Generic', 'Tablet', '50/12.5mg', 'PH-LOS-HCT-50', 12),
(85, 'Losartan + Hydrochlorothiazide', 'Generic', 'Tablet', '100/25mg', 'PH-LOS-HCT-100', 12),
(86, 'Methylergometrine', 'Generic', 'Tablet', '125mcg', 'PH-METH-125', 12),
(87, 'Naproxen', 'Generic', 'Tablet', '500mg', 'PH-NAP-500', 1),
(88, 'Naproxen', 'Generic', 'Tablet', '550mg', 'PH-NAP-550', 1),
(89, 'Nifedipine', 'Generic', 'Capsule', '5mg', 'PH-NIFE-5', 12),
(90, 'Nifedipine', 'Generic', 'Capsule', '10mg', 'PH-NIFE-10', 12),
(91, 'ORS', 'Generic', 'Powder Sachet', '—', 'PH-ORS', 10),
(92, 'Potassium Citrate', 'Generic', 'Tablet', '1.08g', 'PH-POTCIT-1080', 18),
(93, 'Prednisone', 'Generic', 'Tablet', '5mg', 'PH-PRED-5', 20),
(94, 'Prednisone', 'Generic', 'Tablet', '10mg', 'PH-PRED-10', 20),
(95, 'Prednisone', 'Generic', 'Tablet', '20mg', 'PH-PRED-20', 20),
(96, 'Rosuvastatin', 'Generic', 'Tablet', '10mg', 'PH-ROSU-10', 14),
(97, 'Simvastatin', 'Generic', 'Tablet', '10mg', 'PH-SIMVA-10', 14),
(98, 'Simvastatin', 'Generic', 'Tablet', '20mg', 'PH-SIMVA-20', 14),
(99, 'Simvastatin', 'Generic', 'Tablet', '40mg', 'PH-SIMVA-40', 14),
(100, 'Salbutamol', 'Generic', 'Inhaler', '100mcg', 'PH-SAL-INH', 8),
(101, 'Salbutamol', 'Generic', 'Nebule', '2.5mg', 'PH-SAL-NEB', 8),
(102, 'Ambroxol', 'Generic', 'Syrup', '15mg', 'PH-AMBR-15', 7),
(103, 'Ambroxol', 'Generic', 'Syrup', '30mg', 'PH-AMBR-30', 7),
(104, 'Ambroxol', 'Generic', 'Drops', '—', 'PH-AMBR-DROP', 7),
(105, 'Amoxicillin', 'Generic', 'Drops', '100mg', 'PH-AMOX-DROP', 3),
(106, 'Amoxicillin', 'Generic', 'Suspension', '125mg', 'PH-AMOX-125', 3),
(107, 'Amoxicillin', 'Generic', 'Suspension', '250mg', 'PH-AMOX-250S', 3),
(108, 'Ascorbic Acid', 'Generic', 'Drops', '—', 'PH-ASC-DROP', 17),
(109, 'Ascorbic Acid', 'Generic', 'Syrup', '—', 'PH-ASC-SYR', 17),
(110, 'Ascorbic Acid + Zinc', 'Generic', 'Syrup', '—', 'PH-ASC-ZINC', 17),
(111, 'Azithromycin', 'Generic', 'Suspension', '200mg', 'PH-AZI-200', 3),
(112, 'Bromhexine', 'Generic', 'Syrup', '—', 'PH-BROM-SYR', 7),
(113, 'Carbocisteine', 'Generic', 'Syrup', '—', 'PH-CARBO-SYR', 7),
(114, 'Carbocisteine', 'Generic', 'Drops', '—', 'PH-CARBO-DROP', 7),
(115, 'Cefaclor', 'Generic', 'Suspension', '—', 'PH-CEFACL-SUS', 3),
(116, 'Cefaclor', 'Generic', 'Drops', '—', 'PH-CEFACL-DROP', 3),
(117, 'Cetirizine', 'Generic', 'Drops', '—', 'PH-CET-DROP', 6),
(118, 'Cetirizine', 'Generic', 'Syrup', '—', 'PH-CET-SYR', 6),
(119, 'Chlorphenamine', 'Generic', 'Syrup', '—', 'PH-CHLOR-SYR', 6),
(120, 'Co-amoxiclav', 'Generic', 'Suspension', '—', 'PH-COAMOX-SUS', 3),
(121, 'Cotrimoxazole', 'Generic', 'Suspension', '—', 'PH-COTRI-SUS', 3),
(122, 'Diphenhydramine', 'Generic', 'Syrup', '—', 'PH-DIPH-SYR', 6),
(123, 'Ferrous Sulfate', 'Generic', 'Drops', '—', 'PH-FE-DROP', 18),
(124, 'Ferrous Sulfate', 'Generic', 'Syrup', '—', 'PH-FE-SYR', 18),
(125, 'Guaifenesin', 'Generic', 'Syrup', '—', 'PH-GUAI-SYR', 7),
(126, 'Ibuprofen', 'Generic', 'Suspension', '—', 'PH-IBU-SUS', 1),
(127, 'Lagundi', 'Generic', 'Syrup', '—', 'PH-LAG-SYR', 19),
(128, 'Mefenamic Acid', 'Generic', 'Suspension', '—', 'PH-MEF-SUS', 1),
(129, 'Metronidazole', 'Generic', 'Suspension', '—', 'PH-METRO-SUS', 3),
(130, 'Multivitamins', 'Generic', 'Syrup', '—', 'PH-MULTI-SYR', 17),
(131, 'Nystatin', 'Generic', 'Drops', '—', 'PH-NYST-DROP', 5),
(132, 'Paracetamol', 'Generic', 'Syrup', '125mg', 'PH-PARA-125', 1),
(133, 'Paracetamol', 'Generic', 'Syrup', '250mg', 'PH-PARA-250', 1),
(134, 'Paracetamol', 'Generic', 'Drops', '—', 'PH-PARA-DROP', 1),
(135, 'Salbutamol', 'Generic', 'Syrup', '—', 'PH-SAL-SYR', 8),
(136, 'Salbutamol + Guaifenesin', 'Generic', 'Syrup', '—', 'PH-SAL-GUAI', 8),
(137, 'Zinc Sulfate', 'Generic', 'Drops', '—', 'PH-ZINC-DROP', 18),
(138, 'Zinc Sulfate', 'Generic', 'Syrup', '—', 'PH-ZINC-SYR', 18),
(140, 'Paracetamol', 'Rexidol Forte', 'Tablet', '500mg', 'BR-REX-500', 1),
(141, 'Ibuprofen', 'Medicol', 'Tablet', '200mg', 'BR-MEDI-200', 1),
(142, 'Ibuprofen', 'Medicol', 'Tablet', '400mg', 'BR-MEDI-400', 1),
(143, 'Paracetamol + Caffeine', 'Saridon', 'Tablet', 'Standard', 'BR-SARI', 1),
(144, 'Mefenamic Acid', 'Dolfenal', 'Capsule', '500mg', 'BR-DOLF-500', 1),
(145, 'Mefenamic Acid', 'RM Mefenamic', 'Capsule', '500mg', 'BR-RM-MEF', 1),
(146, 'Naproxen Sodium', 'Skelan', 'Tablet', '600mg', 'BR-SKEL-600', 1),
(147, 'Naproxen Sodium', 'Flanax', 'Tablet', '550mg', 'BR-FLAN-550', 1),
(148, 'Ibuprofen', 'Advil', 'Tablet', '200mg', 'BR-ADV-200', 1),
(149, 'Multivitamins', 'Robust', 'Tablet', 'Regular', 'BR-ROB-REG', 17),
(150, 'Multivitamins', 'Robust Extreme', 'Capsule', 'Adult', 'BR-ROB-EXT', 17),
(151, 'Metoprolol', 'Neobloc', 'Tablet', '50mg', 'BR-NEO-50', 12),
(152, 'Metoprolol', 'Neobloc', 'Tablet', '100mg', 'BR-NEO-100', 12),
(153, 'Dimenhydrinate', 'Bonamin Kid', 'Tablet', 'Pediatric', 'BR-BONA-K', 6),
(154, 'Dimenhydrinate', 'Bonamin Adult', 'Tablet', '50mg', 'BR-BONA-A', 6),
(155, 'Loperamide', 'Imodium', 'Capsule', '2mg', 'BR-IMO', 10),
(156, 'Diphenoxylate + Atropine', 'Lomotil', 'Tablet', 'Standard', 'BR-LOMO', 10),
(157, 'Loperamide', 'Diatabs', 'Capsule', '2mg', 'BR-DIA', 10),
(158, 'Multivitamins', 'Kiddelets', 'Tablet', 'Pediatric', 'BR-KID', 27),
(159, 'Paracetamol', 'Tempra', 'Tablet', '325mg', 'BR-TEMP-325', 2),
(160, 'Paracetamol', 'Tempra', 'Tablet', '500mg', 'BR-TEMP-500', 2),
(161, 'Aluminum + Magnesium', 'Kremil S', 'Tablet', 'Standard', 'BR-KREM-S', 9),
(162, 'Aluminum + Magnesium', 'Kremil S Advance', 'Tablet', 'Advance', 'BR-KREM-A', 9),
(163, 'Antacid', 'Gaviscon', 'Sachet', 'Standard', 'BR-GAV-S', 9),
(164, 'Antacid', 'Gaviscon Double Action', 'Tablet', 'Standard', 'BR-GAV-D', 9),
(165, 'Probiotics', 'Erceflora', 'Capsule', 'Standard', 'BR-ERCE', 33),
(166, 'Paracetamol', 'Biogesic', 'Tablet', '500mg', 'BR-BIO-500', 1),
(167, 'Ibuprofen + Paracetamol', 'Alaxan FR', 'Tablet', 'Standard', 'BR-ALAX', 1),
(168, 'Cold Combination', 'Bioflu', 'Tablet', 'Standard', 'BR-BIOFLU', 7),
(169, 'Cold Combination', 'Neozep Forte', 'Tablet', 'Standard', 'BR-NEO-F', 7),
(170, 'Cold Combination', 'Decolgen Forte', 'Tablet', 'Standard', 'BR-DECO-F', 7),
(171, 'Cold Combination', 'Decolgen Non-Drowsy', 'Tablet', 'Standard', 'BR-DECO-ND', 7),
(172, 'Cough Suppressant', 'Robitussin', 'Capsule', 'Standard', 'BR-ROBI', 7),
(173, 'Food Supplement', 'MX3', 'Capsule', 'Standard', 'BR-MX3', 33),
(174, 'Food Supplement', 'Xanthon Plus Gold', 'Capsule', 'Standard', 'BR-XANTH', 33),
(175, 'Cold Combination', 'Tuseran', 'Tablet', 'Standard', 'BR-TUSE', 7),
(176, 'Carbocisteine', 'Solmux', 'Capsule', 'Standard', 'BR-SOL-C', 7),
(177, 'Carbocisteine', 'Solmux Advance', 'Capsule', 'Advance', 'BR-SOL-A', 7),
(178, 'Antiseptic', 'Dequadin', 'Tablet', 'Standard', 'BR-DEQUA', 21),
(179, 'Calcium + Vitamins', 'Caltrate Advance', 'Tablet', 'Standard', 'BR-CALTR', 18),
(180, 'Multivitamins + Iron', 'Cherifer PGM', 'Tablet', 'Standard', 'BR-CHER-P', 26),
(181, 'Vitamin B-Complex', 'Pharex B', 'Tablet', 'Standard', 'BR-PHAR-B', 17),
(182, 'Vitamin E', 'Neurogen E', 'Capsule', 'Standard', 'BR-NEURO', 17),
(183, 'Multivitamins', 'Centrum Advance', 'Tablet', 'Adult', 'BR-CENT', 17),
(184, 'Vitamin E', 'Myra E', 'Capsule', '400IU', 'BR-MYRA', 17),
(185, 'Multivitamins + Zinc', 'Enervon Z+', 'Tablet', 'Standard', 'BR-ENERV', 17),
(186, 'Multivitamins', 'Revicon', 'Tablet', 'Standard', 'BR-REV', 17),
(187, 'Multivitamins + Minerals', 'Conzace', 'Capsule', 'Standard', 'BR-CONZ', 17),
(188, 'Multivitamins', 'Stress Tab', 'Tablet', 'Standard', 'BR-STRESS', 17),
(189, 'Appetite Stimulant', 'Propan w/ Iron', 'Capsule', 'Standard', 'BR-PROP-C', 26),
(190, 'Food Supplement', 'Memoplus Gold', 'Capsule', 'Standard', 'BR-MEMO', 33),
(191, 'Multivitamins + Iron', 'Cherifer', 'Drops', '15ml', 'BR-CHER-D', 27),
(192, 'Multivitamins + Iron', 'Cherifer', 'Syrup', '120ml', 'BR-CHER-S', 27),
(193, 'Appetite Stimulant', 'Propan TLC', 'Drops', '15ml', 'BR-PROP-D', 27),
(194, 'Appetite Stimulant', 'Propan TLC', 'Syrup', '120ml', 'BR-PROP-S', 27),
(195, 'Multivitamins', 'Nutroplex', 'Syrup', '120ml', 'BR-NUTRO', 27),
(196, 'Multivitamins', 'Tiki-Tiki', 'Drops', '15ml', 'BR-TIKI-15', 27),
(197, 'Multivitamins', 'Tiki-Tiki', 'Drops', '30ml', 'BR-TIKI-30', 27),
(198, 'Multivitamins', 'Tiki-Tiki', 'Syrup', '120ml', 'BR-TIKI-120', 27),
(199, 'Iron Supplement', 'Nutrilin', 'Drops', '15ml', 'BR-NUTRI-D', 27),
(200, 'Iron Supplement', 'Nutrilin', 'Syrup', '120ml', 'BR-NUTRI-S', 27),
(201, 'Vitamin C', 'Ceelin', 'Drops', '15ml', 'BR-CEE-15', 27),
(202, 'Vitamin C', 'Ceelin', 'Drops', '30ml', 'BR-CEE-30', 27),
(203, 'Vitamin C', 'Ceelin', 'Syrup', '60ml', 'BR-CEE-60', 27),
(204, 'Vitamin C', 'Ceelin', 'Syrup', '120ml', 'BR-CEE-120', 27),
(205, 'Vitamin C + Zinc', 'Ceelin Plus', 'Drops', '15ml', 'BR-CEEP-15', 27),
(206, 'Vitamin C + Zinc', 'Ceelin Plus', 'Drops', '30ml', 'BR-CEEP-30', 27),
(207, 'Vitamin C + Zinc', 'Ceelin Plus', 'Syrup', '60ml', 'BR-CEEP-60', 27),
(208, 'Vitamin C + Zinc', 'Ceelin Plus', 'Syrup', '120ml', 'BR-CEEP-120', 27),
(209, 'Saline', 'Salinase', 'Drops', '30ml', 'BR-SAL-D', 31),
(210, 'Saline', 'Salinase', 'Spray', '30ml', 'BR-SAL-SP', 31),
(211, 'Carbocisteine', 'Solmux', 'Drops', '15ml', 'BR-SOL-D', 7),
(212, 'Carbocisteine', 'Solmux', 'Syrup', '100mg', 'BR-SOL-100', 7),
(213, 'Carbocisteine', 'Solmux', 'Syrup', '200mg', 'BR-SOL-200', 7),
(214, 'Cold Combination', 'Disudrin', 'Drops', '10ml', 'BR-DIS-D', 7),
(215, 'Cold Combination', 'Disudrin', 'Syrup', '60ml', 'BR-DIS-S', 7),
(216, 'Cold Combination', 'Neozep', 'Drops', '10ml', 'BR-NEO-D', 7),
(217, 'Cold Combination', 'Neozep', 'Syrup', '60ml', 'BR-NEO-S', 7),
(218, 'Multivitamins', 'Plemex Kid', 'Syrup', '60ml', 'BR-PLEM-K', 27),
(219, 'Multivitamins', 'Plemex Forte', 'Syrup', '60ml', 'BR-PLEM-F', 27),
(220, 'Paracetamol', 'Calpol', 'Drops', '10ml', 'BR-CAL-D', 2),
(221, 'Paracetamol', 'Calpol', 'Syrup', '120mg', 'BR-CAL-120', 2),
(222, 'Paracetamol', 'Calpol', 'Syrup', '250mg', 'BR-CAL-250', 2),
(223, 'Paracetamol', 'Biogesic', 'Drops', '15ml', 'BR-BIO-D', 2),
(224, 'Paracetamol', 'Biogesic', 'Syrup', '120mg', 'BR-BIO-120', 2),
(225, 'Paracetamol', 'Biogesic', 'Syrup', '250mg', 'BR-BIO-250', 2),
(226, 'Paracetamol', 'Tempra', 'Drops', '15ml', 'BR-TEMP-D', 2),
(227, 'Paracetamol', 'Tempra', 'Syrup', '120mg', 'BR-TEMP-120', 2),
(228, 'Paracetamol', 'Tempra', 'Syrup', '250mg', 'BR-TEMP-250', 2);

-- --------------------------------------------------------

--
-- Table structure for table `medicine_batch`
--

CREATE TABLE `medicine_batch` (
  `batch_id` int(11) NOT NULL,
  `medicine_id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `expiry_date` date NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medicine_batch`
--

INSERT INTO `medicine_batch` (`batch_id`, `medicine_id`, `branch_id`, `supplier_id`, `expiry_date`, `quantity`, `unit_price`) VALUES
(1, 1, 1, 1, '2026-12-31', 100, 8.50),
(2, 2, 1, 1, '2026-11-30', 80, 9.00),
(3, 3, 1, 1, '2027-01-31', 120, 10.00),
(4, 4, 1, 1, '2026-10-15', 60, 7.50),
(5, 5, 1, 1, '2027-03-31', 90, 11.00),
(6, 6, 1, 1, '2026-09-30', 70, 6.75),
(7, 7, 1, 1, '2027-06-30', 150, 12.00),
(8, 8, 1, 1, '2026-08-31', 50, 5.50),
(9, 9, 1, 1, '2027-04-30', 200, 13.25),
(10, 10, 1, 1, '2026-12-15', 110, 9.75);

-- --------------------------------------------------------

--
-- Table structure for table `medicine_in_branch`
--

CREATE TABLE `medicine_in_branch` (
  `medicine_branch_id` int(11) NOT NULL,
  `medicine_id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `total_quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medicine_in_branch`
--

INSERT INTO `medicine_in_branch` (`medicine_branch_id`, `medicine_id`, `branch_id`, `total_quantity`) VALUES
(1, 1, 1, 100),
(2, 2, 1, 80),
(3, 3, 1, 120),
(4, 4, 1, 60),
(5, 5, 1, 90),
(6, 6, 1, 70),
(7, 7, 1, 150),
(8, 8, 1, 50),
(9, 9, 1, 200),
(10, 10, 1, 110);

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(150) NOT NULL,
  `dosage_form` varchar(50) DEFAULT NULL,
  `strength` varchar(50) DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `barcode` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`product_id`, `product_name`, `dosage_form`, `strength`, `category_id`, `unit`, `barcode`) VALUES
(1, 'Ampalaya', 'Capsule', NULL, 33, 'Bottle', 'PROD-AMP-500'),
(2, 'Malunggay', 'Capsule', NULL, 33, 'Bottle', 'PROD-MAL-500'),
(3, 'Evening Primrose Oil ', 'Capsule', NULL, 33, 'Bottle', 'PROD-EPO-1000'),
(4, 'Sambong ', 'Capsule', NULL, 33, 'Bottle', 'PROD-SAM-500'),
(5, 'Vitamin B Complex ', 'Tablet', NULL, 34, 'Bottle', 'PROD-VBC-100'),
(6, 'Vitamin B Complex ', 'Capsule', NULL, 34, 'Bottle', 'PROD-VBC-500'),
(7, 'Ascorbic Acid as Sodium Ascorbate', 'Capsule', NULL, 34, 'Bottle', 'PROD-ASC-SOD'),
(8, 'Sodium Ascorbate + Zinc Capsule', 'Capsule', NULL, 34, 'Bottle', 'PROD-ASC-ZINC'),
(9, 'Multivitamins + Iron (Ironvit)', 'Capsule', NULL, 34, 'Bottle', 'PROD-IRONVIT'),
(10, 'Ferrous + Folic Acid + B-Complex ', 'Capsule', NULL, 34, 'Bottle', 'PROD-FE-FB'),
(11, 'Ferrous Sulfate ', 'Capsule', NULL, 34, 'Bottle', 'PROD-FE-500'),
(12, 'Multivitamins + Minerals Tablet (Eurivit)', 'Tablet', NULL, 34, 'Bottle', 'PROD-EURIVIT'),
(13, 'Multivitamins + Buclizine + Iron (Appebos)', 'Capsule', NULL, 34, 'Bottle', 'PROD-APPEBOS'),
(14, 'Multivitamins', 'Capsule', NULL, 34, 'Bottle', 'PROD-MULTI'),
(15, 'Betamethasone ', 'Cream', NULL, 35, 'Tube', 'PROD-BETA-CRM'),
(16, 'Clobetasol', 'Cream', NULL, 35, 'Tube', 'PROD-CLOB-CRM'),
(17, 'Hydrocortisone', 'Cream', NULL, 35, 'Tube', 'PROD-HYDRO-CRM'),
(18, 'Ketoconazole', 'Cream', NULL, 35, 'Tube', 'PROD-KETO-CRM'),
(19, 'Mupirocin', 'Ointment', NULL, 35, 'Tube', 'PROD-MUPI-OINT'),
(20, 'Silver Sulfadiazine', 'Cream', NULL, 35, 'Tube', ''),
(21, 'Tobramycin Eye Drops', 'Topical', NULL, 35, 'Bottle', 'PROD-TOBRA-EYE'),
(22, 'Digital Thermometer', NULL, NULL, 32, 'Piece', 'PROD-THERMO'),
(23, 'Elastic Bandage 3in x 5', NULL, NULL, 32, 'Roll', 'PROD-ELASTIC'),
(24, 'Gauze Pad 4in x 4', NULL, NULL, 32, 'Pack', 'PROD-GPAD'),
(25, 'Gauze Roll 4in x 10', NULL, NULL, 32, 'Roll', 'PROD-GROLL'),
(26, 'Hydrogen Peroxide', 'Null', NULL, 32, 'Bottle', 'PROD-HPO-120'),
(27, 'Hydrogen Peroxide', 'Solution', NULL, 32, 'Bottle', 'PROD-HPO-60'),
(28, 'Medicine Cup', NULL, NULL, 32, 'Piece', 'PROD-MED-CUP'),
(29, 'Medicine Dropper', NULL, NULL, 32, 'Piece', 'PROD-DROPPER'),
(30, 'Nebulizer Kit (Ordinary)', NULL, NULL, 32, 'Set', 'PROD-NEB-KIT'),
(31, 'Povidone Iodine', 'Solution', NULL, 32, 'Bottle', 'PROD-PVI-15'),
(32, 'Povidone Iodine', 'Solution', NULL, 32, 'Bottle', 'PROD-PVI-30'),
(33, 'Povidone Iodine', 'Solution', NULL, 32, 'Bottle', 'PROD-PVI-60'),
(34, 'Pregnancy Test', NULL, NULL, 32, 'Kit', 'PROD-PREG-TEST'),
(35, 'Specimen Cup', NULL, NULL, 32, 'Piece', 'PROD-SPEC-CUP'),
(36, 'Surgical Gloves Size 7', NULL, NULL, 32, 'Pair', 'PROD-GLOVE-7'),
(37, 'Surgical Tape 1 inch', NULL, NULL, 32, 'Roll', 'PROD-TAPE-1'),
(38, 'Surgical Tape 1/2 inch', NULL, NULL, 32, 'Roll', 'PROD-TAPE-05'),
(39, 'Syringe 1cc', NULL, NULL, 32, 'Piece', 'PROD-SYR-1'),
(40, 'Syringe 3cc', NULL, NULL, 32, 'Piece', 'PROD-SYR-3'),
(41, 'Syringe 5cc', NULL, NULL, 32, 'Piece', 'PROD-SYR-5'),
(42, 'Syringe 10cc', NULL, NULL, 32, 'Piece', 'PROD-SYR-10'),
(43, 'Urine Collector (Pediatric)', NULL, NULL, 32, 'Piece', 'PROD-URINE-PED');

-- --------------------------------------------------------

--
-- Table structure for table `product_in_branch`
--

CREATE TABLE `product_in_branch` (
  `product_branch_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `selling_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order`
--

CREATE TABLE `purchase_order` (
  `po_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `order_date` datetime NOT NULL,
  `status` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order_items`
--

CREATE TABLE `purchase_order_items` (
  `po_item_id` int(11) NOT NULL,
  `po_id` int(11) NOT NULL,
  `medicine_id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`) VALUES
(1, 'Owner'),
(2, 'Manager'),
(3, 'Cashier');

-- --------------------------------------------------------

--
-- Table structure for table `stock_transfer`
--

CREATE TABLE `stock_transfer` (
  `transfer_id` int(11) NOT NULL,
  `from_branch_id` int(11) NOT NULL,
  `to_branch_id` int(11) NOT NULL,
  `transfer_date` datetime NOT NULL,
  `status` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_transfer_items`
--

CREATE TABLE `stock_transfer_items` (
  `transfer_item_id` int(11) NOT NULL,
  `transfer_id` int(11) NOT NULL,
  `medicine_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplier`
--

CREATE TABLE `supplier` (
  `supplier_id` int(11) NOT NULL,
  `supplier_name` varchar(100) NOT NULL,
  `contact_name` varchar(150) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplier`
--

INSERT INTO `supplier` (`supplier_id`, `supplier_name`, `contact_name`, `contact_number`) VALUES
(1, 'GDMED PHARMA DISTRIBUTION', 'Sean Doctor', '+1-555-0103'),
(2, 'HLS Sales Marketing', 'Kyle Remigio', '+1-555-0102');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL,
  `branch_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `role_id`, `branch_id`) VALUES
(1, 'owner_admin', '$2y$10$guV8U9pfgEkzWU.Sy1cZCO7K9cmTlQac01oBJGEd7zfxAkmwnlNii', 1, NULL),
(2, 'manager_b1', 'pass123', 2, 1),
(3, 'manager_b2', 'pass123', 2, 2),
(4, 'manager_b3', 'pass123', 2, 3),
(5, 'po_b1', 'pass123', 3, 1),
(6, 'po_b2', 'pass123', 3, 2),
(7, 'po_b3', 'pass123', 3, 3);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `branch`
--
ALTER TABLE `branch`
  ADD PRIMARY KEY (`branch_id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `invoice`
--
ALTER TABLE `invoice`
  ADD PRIMARY KEY (`invoice_id`),
  ADD KEY `branch_id` (`branch_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`invoice_item_id`),
  ADD KEY `invoice_id` (`invoice_id`),
  ADD KEY `batch_id` (`batch_id`);

--
-- Indexes for table `medicine`
--
ALTER TABLE `medicine`
  ADD PRIMARY KEY (`medicine_id`),
  ADD UNIQUE KEY `barcode` (`barcode`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `medicine_batch`
--
ALTER TABLE `medicine_batch`
  ADD PRIMARY KEY (`batch_id`),
  ADD KEY `medicine_id` (`medicine_id`),
  ADD KEY `branch_id` (`branch_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `medicine_in_branch`
--
ALTER TABLE `medicine_in_branch`
  ADD PRIMARY KEY (`medicine_branch_id`),
  ADD UNIQUE KEY `medicine_id` (`medicine_id`,`branch_id`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`product_id`),
  ADD UNIQUE KEY `barcode` (`barcode`),
  ADD KEY `fk_product_category` (`category_id`);

--
-- Indexes for table `product_in_branch`
--
ALTER TABLE `product_in_branch`
  ADD PRIMARY KEY (`product_branch_id`),
  ADD UNIQUE KEY `product_id` (`product_id`,`branch_id`),
  ADD KEY `fk_pib_branch` (`branch_id`);

--
-- Indexes for table `purchase_order`
--
ALTER TABLE `purchase_order`
  ADD PRIMARY KEY (`po_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD PRIMARY KEY (`po_item_id`),
  ADD KEY `po_id` (`po_id`),
  ADD KEY `medicine_id` (`medicine_id`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`);

--
-- Indexes for table `stock_transfer`
--
ALTER TABLE `stock_transfer`
  ADD PRIMARY KEY (`transfer_id`),
  ADD KEY `from_branch_id` (`from_branch_id`),
  ADD KEY `to_branch_id` (`to_branch_id`);

--
-- Indexes for table `stock_transfer_items`
--
ALTER TABLE `stock_transfer_items`
  ADD PRIMARY KEY (`transfer_item_id`),
  ADD KEY `transfer_id` (`transfer_id`),
  ADD KEY `medicine_id` (`medicine_id`);

--
-- Indexes for table `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`supplier_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `branch_id` (`branch_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `branch`
--
ALTER TABLE `branch`
  MODIFY `branch_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `invoice`
--
ALTER TABLE `invoice`
  MODIFY `invoice_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `invoice_item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medicine`
--
ALTER TABLE `medicine`
  MODIFY `medicine_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=229;

--
-- AUTO_INCREMENT for table `medicine_batch`
--
ALTER TABLE `medicine_batch`
  MODIFY `batch_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `medicine_in_branch`
--
ALTER TABLE `medicine_in_branch`
  MODIFY `medicine_branch_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `product_in_branch`
--
ALTER TABLE `product_in_branch`
  MODIFY `product_branch_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_order`
--
ALTER TABLE `purchase_order`
  MODIFY `po_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  MODIFY `po_item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `stock_transfer`
--
ALTER TABLE `stock_transfer`
  MODIFY `transfer_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_transfer_items`
--
ALTER TABLE `stock_transfer_items`
  MODIFY `transfer_item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supplier`
--
ALTER TABLE `supplier`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `invoice`
--
ALTER TABLE `invoice`
  ADD CONSTRAINT `invoice_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branch` (`branch_id`),
  ADD CONSTRAINT `invoice_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD CONSTRAINT `invoice_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoice` (`invoice_id`),
  ADD CONSTRAINT `invoice_items_ibfk_2` FOREIGN KEY (`batch_id`) REFERENCES `medicine_batch` (`batch_id`);

--
-- Constraints for table `medicine`
--
ALTER TABLE `medicine`
  ADD CONSTRAINT `medicine_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`);

--
-- Constraints for table `medicine_batch`
--
ALTER TABLE `medicine_batch`
  ADD CONSTRAINT `medicine_batch_ibfk_1` FOREIGN KEY (`medicine_id`) REFERENCES `medicine` (`medicine_id`),
  ADD CONSTRAINT `medicine_batch_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branch` (`branch_id`),
  ADD CONSTRAINT `medicine_batch_ibfk_3` FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`supplier_id`);

--
-- Constraints for table `medicine_in_branch`
--
ALTER TABLE `medicine_in_branch`
  ADD CONSTRAINT `medicine_in_branch_ibfk_1` FOREIGN KEY (`medicine_id`) REFERENCES `medicine` (`medicine_id`),
  ADD CONSTRAINT `medicine_in_branch_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branch` (`branch_id`);

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `fk_product_category` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`);

--
-- Constraints for table `product_in_branch`
--
ALTER TABLE `product_in_branch`
  ADD CONSTRAINT `fk_pib_branch` FOREIGN KEY (`branch_id`) REFERENCES `branch` (`branch_id`),
  ADD CONSTRAINT `fk_pib_product` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`);

--
-- Constraints for table `purchase_order`
--
ALTER TABLE `purchase_order`
  ADD CONSTRAINT `purchase_order_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`supplier_id`);

--
-- Constraints for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD CONSTRAINT `purchase_order_items_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `purchase_order` (`po_id`),
  ADD CONSTRAINT `purchase_order_items_ibfk_2` FOREIGN KEY (`medicine_id`) REFERENCES `medicine` (`medicine_id`),
  ADD CONSTRAINT `purchase_order_items_ibfk_3` FOREIGN KEY (`branch_id`) REFERENCES `branch` (`branch_id`);

--
-- Constraints for table `stock_transfer`
--
ALTER TABLE `stock_transfer`
  ADD CONSTRAINT `stock_transfer_ibfk_1` FOREIGN KEY (`from_branch_id`) REFERENCES `branch` (`branch_id`),
  ADD CONSTRAINT `stock_transfer_ibfk_2` FOREIGN KEY (`to_branch_id`) REFERENCES `branch` (`branch_id`);

--
-- Constraints for table `stock_transfer_items`
--
ALTER TABLE `stock_transfer_items`
  ADD CONSTRAINT `stock_transfer_items_ibfk_1` FOREIGN KEY (`transfer_id`) REFERENCES `stock_transfer` (`transfer_id`),
  ADD CONSTRAINT `stock_transfer_items_ibfk_2` FOREIGN KEY (`medicine_id`) REFERENCES `medicine` (`medicine_id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`),
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branch` (`branch_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
