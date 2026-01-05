-- Money Exchange System Database Schema
-- Created for Diamond Group Money Exchange
-- Supports multi-currency transactions and comprehensive reporting

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- Database: `exchange_system`

CREATE DATABASE IF NOT EXISTS `exchange_system` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `exchange_system`;

-- --------------------------------------------------------

-- Table structure for table `currencies`
CREATE TABLE IF NOT EXISTS `currencies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(10) NOT NULL,
  `name_en` varchar(100) NOT NULL,
  `name_ku` varchar(100) NOT NULL,
  `name_ar` varchar(100) NOT NULL,
  `symbol` varchar(20) NOT NULL,
  `exchange_rate_to_usd` decimal(15,6) DEFAULT 1.000000,
  `exchange_rate_to_iqd` decimal(15,6) DEFAULT 1.000000,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert all supported currencies
INSERT INTO `currencies` (`code`, `name_en`, `name_ku`, `name_ar`, `symbol`, `exchange_rate_to_usd`, `exchange_rate_to_iqd`) VALUES
('IQD', 'Iraqi Dinar', 'دیناری عێراقی', 'دينار عراقي', 'د.ع', 0.000769, 1.000000),
('USD', 'United States Dollar', 'دۆلاری ئەمریکا', 'دولار أمريكي', '$', 1.000000, 1300.000000),
('EUR', 'Euro', 'یۆرۆ', 'يورو', '€', 1.100000, 1430.000000),
('GBP', 'British Pound', 'پاوەندی بەریتانی', 'جنيه استرليني', '£', 1.270000, 1651.000000),
('ALL', 'Albanian Lek', 'لێکی ئەڵبانی', 'ليك ألباني', 'Lek', 0.010800, 14.040000),
('AFN', 'Afghan Afghani', 'ئەفغانی ئەفغانستان', 'أفغاني', '؋', 0.014500, 18.850000),
('ARS', 'Argentine Peso', 'پێسۆی ئەرجەنتین', 'بيزو أرجنتيني', '$', 0.005000, 6.500000),
('AWG', 'Aruban Florin', 'فلۆرینی ئارووبا', 'فلورين أروبا', 'ƒ', 0.556000, 722.800000),
('AUD', 'Australian Dollar', 'دۆلاری ئۆسترالیا', 'دولار أسترالي', '$', 0.680000, 884.000000),
('AZN', 'Azerbaijani Manat', 'مانەتی ئازەربایجان', 'مانات أذربيجاني', '₼', 0.588000, 764.400000),
('BSD', 'Bahamian Dollar', 'دۆلاری بەهاما', 'دولار بهاما', '$', 1.000000, 1300.000000),
('BBD', 'Barbadian Dollar', 'دۆلاری باربادۆس', 'دولار بربادوس', '$', 0.500000, 650.000000),
('BYN', 'Belarusian Ruble', 'ڕوبڵی بێلاڕووس', 'روبل بيلاروسي', 'Br', 0.398000, 517.400000),
('BZD', 'Belize Dollar', 'دۆلاری بێلیز', 'دولار بليز', 'BZ$', 0.496000, 644.800000),
('BMD', 'Bermudan Dollar', 'دۆلاری بێرمودا', 'دولار برمودا', '$', 1.000000, 1300.000000),
('BOB', 'Bolivian Boliviano', 'بۆلیڤیانۆی بۆلیڤیا', 'بوليفيانو بوليفي', '$b', 0.145000, 188.500000),
('BAM', 'Bosnia-Herzegovina Convertible Mark', 'مارکی بۆسنیا', 'مارك بوسني', 'KM', 0.562000, 730.600000),
('BWP', 'Botswanan Pula', 'پولای بۆتسوانا', 'بولا بوتسوانا', 'P', 0.076000, 98.800000),
('BRL', 'Brazilian Real', 'ڕێاڵی بەڕازیل', 'ريال برازيلي', 'R$', 0.200000, 260.000000),
('BND', 'Brunei Dollar', 'دۆلاری بڕونای', 'دولار بروناي', '$', 0.742000, 964.600000),
('BGN', 'Bulgarian Lev', 'لێڤی بولگاریا', 'ليف بلغاري', 'лв', 0.562000, 730.600000),
('KHR', 'Cambodian Riel', 'ڕیێلی کەمبۆدیا', 'رييل كمبودي', '៛', 0.000245, 0.318500),
('CAD', 'Canadian Dollar', 'دۆلاری کەنەدا', 'دولار كندي', '$', 0.750000, 975.000000),
('KYD', 'Cayman Islands Dollar', 'دۆلاری دوورگەکانی کایمان', 'دولار جزر كايمان', '$', 1.200000, 1560.000000),
('CLP', 'Chilean Peso', 'پێسۆی چیلی', 'بيزو تشيلي', '$', 0.001200, 1.560000),
('CNY', 'Chinese Yuan', 'یوانی چین', 'يوان صيني', '¥', 0.140000, 182.000000),
('COP', 'Colombian Peso', 'پێسۆی کۆلۆمبیا', 'بيزو كولومبي', '$', 0.000260, 0.338000),
('CRC', 'Costa Rican Colón', 'کۆلۆنی کۆستاریکا', 'كولون كوستاريكا', '₡', 0.001950, 2.535000),
('HRK', 'Croatian Kuna', 'کوونای کرۆواتیا', 'كونا كرواتي', 'kn', 0.146000, 189.800000),
('CUP', 'Cuban Peso', 'پێسۆی کووبا', 'بيزو كوبي', '₱', 0.042000, 54.600000),
('CZK', 'Czech Koruna', 'کۆرونای چیک', 'كورونا تشيكي', 'Kč', 0.045000, 58.500000),
('DKK', 'Danish Krone', 'کرۆنی دانمارک', 'كرونة دنماركية', 'kr', 0.147000, 191.100000),
('DOP', 'Dominican Peso', 'پێسۆی دۆمینیکان', 'بيزو دومينيكاني', 'RD$', 0.018000, 23.400000),
('XCD', 'East Caribbean Dollar', 'دۆلاری ڕۆژهەڵاتی کاریبی', 'دولار شرق الكاريبي', '$', 0.370000, 481.000000),
('EGP', 'Egyptian Pound', 'پاوەندی میسر', 'جنيه مصري', '£', 0.033000, 42.900000),
('SVC', 'Salvadoran Colón', 'کۆلۆنی سالڤادۆر', 'كولون سلفادوري', '$', 0.114000, 148.200000),
('FKP', 'Falkland Islands Pound', 'پاوەندی دوورگەکانی فۆکلاند', 'جنيه جزر فوكلاند', '£', 1.270000, 1651.000000),
('FJD', 'Fijian Dollar', 'دۆلاری فیجی', 'دولار فيجي', '$', 0.456000, 592.800000),
('GHS', 'Ghanaian Cedi', 'سێدی غانا', 'سيدي غانا', '¢', 0.085000, 110.500000),
('GIP', 'Gibraltar Pound', 'پاوەندی گیبراڵتار', 'جنيه جبل طارق', '£', 1.270000, 1651.000000),
('GTQ', 'Guatemalan Quetzal', 'کێتزاڵی گواتیمالا', 'كيتزال غواتيمالا', 'Q', 0.128000, 166.400000),
('GGP', 'Guernsey Pound', 'پاوەندی گێرنسی', 'جنيه غيرنسي', '£', 1.270000, 1651.000000),
('GYD', 'Guyanaese Dollar', 'دۆلاری گویانا', 'دولار غيانا', '$', 0.004800, 6.240000),
('HNL', 'Honduran Lempira', 'لێمپیرای هەندووراس', 'ليمبيرا هندوراس', 'L', 0.041000, 53.300000),
('HKD', 'Hong Kong Dollar', 'دۆلاری هۆنگ کۆنگ', 'دولار هونغ كونغ', '$', 0.128000, 166.400000),
('HUF', 'Hungarian Forint', 'فۆرینتی مەجارستان', 'فورنت مجري', 'Ft', 0.002900, 3.770000),
('ISK', 'Icelandic Króna', 'کرۆنای ئایسلەند', 'كرونة أيسلندية', 'kr', 0.007400, 9.620000),
('INR', 'Indian Rupee', 'ڕوپیی هیند', 'روبية هندية', '₹', 0.012000, 15.600000),
('IDR', 'Indonesian Rupiah', 'ڕوپیای ئیندۆنیزیا', 'روبية إندونيسية', 'Rp', 0.000064, 0.083200),
('IRR', 'Iranian Rial', 'ڕیاڵی ئێران', 'ريال إيراني', '﷼', 0.000024, 0.031200),
('IMP', 'Isle of Man Pound', 'پاوەندی دوورگەی مان', 'جنيه جزيرة مان', '£', 1.270000, 1651.000000),
('ILS', 'Israeli New Shekel', 'شێکڵی ئیسرائیل', 'شيكل إسرائيلي', '₪', 0.295000, 383.500000),
('JMD', 'Jamaican Dollar', 'دۆلاری جامایکا', 'دولار جامايكي', 'J$', 0.006500, 8.450000),
('JPY', 'Japanese Yen', 'یێنی ژاپۆن', 'ين ياباني', '¥', 0.009100, 11.830000),
('JEP', 'Jersey Pound', 'پاوەندی جێرسی', 'جنيه جيرسي', '£', 1.270000, 1651.000000),
('KZT', 'Kazakhstani Tenge', 'تێنگەی کازاخستان', 'تنغي كازاخستاني', 'лв', 0.002200, 2.860000),
('KPW', 'North Korean Won', 'وۆنی کۆریای باکوور', 'وون كوري شمالي', '₩', 0.001110, 1.443000),
('KRW', 'South Korean Won', 'وۆنی کۆریای باشوور', 'وون كوري جنوبي', '₩', 0.000820, 1.066000),
('KGS', 'Kyrgystani Som', 'سۆمی قرغیزستان', 'سوم قيرغيزستاني', 'лв', 0.011500, 14.950000),
('LAK', 'Laotian Kip', 'کیپی لائۆس', 'كيب لاوسي', '₭', 0.000048, 0.062400),
('LBP', 'Lebanese Pound', 'پاوەندی لوبنان', 'ليرة لبنانية', '£', 0.000011, 0.014300),
('LRD', 'Liberian Dollar', 'دۆلاری لیبێریا', 'دولار ليبيري', '$', 0.005300, 6.890000),
('MKD', 'Macedonian Denar', 'دێناری مەقدۆنیا', 'دينار مقدوني', 'ден', 0.018000, 23.400000),
('MYR', 'Malaysian Ringgit', 'ڕینگیتی مالیزیا', 'رينغيت ماليزي', 'RM', 0.225000, 292.500000),
('MUR', 'Mauritian Rupee', 'ڕوپیی مۆریتانیا', 'روبية موريشيوسية', '₨', 0.022000, 28.600000),
('MXN', 'Mexican Peso', 'پێسۆی مەکسیک', 'بيزو مكسيكي', '$', 0.060000, 78.000000),
('MNT', 'Mongolian Tugrik', 'تووگریکی مەنگۆلیا', 'توغريك منغولي', '₮', 0.000295, 0.383500),
('MZN', 'Mozambican Metical', 'مێتیکاڵی مۆزامبیک', 'ميتيكال موزمبيقي', 'MT', 0.016000, 20.800000),
('NAD', 'Namibian Dollar', 'دۆلاری نامیبیا', 'دولار ناميبي', '$', 0.056000, 72.800000),
('NPR', 'Nepalese Rupee', 'ڕوپیی نیپال', 'روبية نيبالية', '₨', 0.007600, 9.880000),
('ANG', 'Netherlands Antillean Guilder', 'گیلدەری ئانتیلی هۆڵەندا', 'غيلدر أنتيلي', 'ƒ', 0.555000, 721.500000),
('NZD', 'New Zealand Dollar', 'دۆلاری نیوزیلاند', 'دولار نيوزيلندي', '$', 0.625000, 812.500000),
('NIO', 'Nicaraguan Córdoba', 'کۆردۆبای نیکاراگوا', 'كوردوبا نيكاراغوا', 'C$', 0.027000, 35.100000),
('NGN', 'Nigerian Naira', 'نایرای نیجێریا', 'نايرا نيجيري', '₦', 0.002400, 3.120000),
('NOK', 'Norwegian Krone', 'کرۆنی نەرویج', 'كرونة نرويجية', 'kr', 0.095000, 123.500000),
('OMR', 'Omani Rial', 'ڕیاڵی عومان', 'ريال عماني', '﷼', 2.600000, 3380.000000),
('PKR', 'Pakistani Rupee', 'ڕوپیی پاکستان', 'روبية باكستانية', '₨', 0.003600, 4.680000),
('PAB', 'Panamanian Balboa', 'بالبۆای پاناما', 'بالبوا بنمي', 'B/.', 1.000000, 1300.000000),
('PYG', 'Paraguayan Guarani', 'گواڕانیی پاراگوای', 'غواراني باراغواي', 'Gs', 0.000135, 0.175500),
('PEN', 'Peruvian Sol', 'سۆڵی پێرۆ', 'سول بيروفي', 'S/.', 0.270000, 351.000000),
('PHP', 'Philippine Peso', 'پێسۆی فلیپین', 'بيزو فلبيني', '₱', 0.018000, 23.400000),
('PLN', 'Polish Zloty', 'زلۆتی پۆڵەندا', 'زلوتي بولندي', 'zł', 0.250000, 325.000000),
('QAR', 'Qatari Rial', 'ڕیاڵی قەتەر', 'ريال قطري', '﷼', 0.275000, 357.500000),
('RON', 'Romanian Leu', 'لیوی ڕۆمانیا', 'ليو روماني', 'lei', 0.220000, 286.000000),
('RUB', 'Russian Ruble', 'ڕوبڵی ڕووسیا', 'روبل روسي', '₽', 0.011000, 14.300000),
('SHP', 'Saint Helena Pound', 'پاوەندی سەینت هێلێنا', 'جنيه سانت هيلينا', '£', 1.270000, 1651.000000),
('SAR', 'Saudi Riyal', 'ڕیاڵی سعوودی', 'ريال سعودي', '﷼', 0.267000, 347.100000),
('RSD', 'Serbian Dinar', 'دیناری سێربیا', 'دينار صربي', 'Дин.', 0.009400, 12.220000),
('SCR', 'Seychellois Rupee', 'ڕوپیی سیشێل', 'روبية سيشيلية', '₨', 0.074000, 96.200000),
('SGD', 'Singapore Dollar', 'دۆلاری سینگاپور', 'دولار سنغافوري', '$', 0.745000, 968.500000),
('SBD', 'Solomon Islands Dollar', 'دۆلاری دوورگەکانی سۆلۆمۆن', 'دولار جزر سليمان', '$', 0.120000, 156.000000),
('SOS', 'Somali Shilling', 'شلینی سۆمالیا', 'شلن صومالي', 'S', 0.001750, 2.275000),
('ZAR', 'South African Rand', 'ڕاندی ئەفریقای باشوور', 'راند جنوب أفريقي', 'R', 0.055000, 71.500000),
('LKR', 'Sri Lankan Rupee', 'ڕوپیی سریلانکا', 'روبية سريلانكية', '₨', 0.003300, 4.290000),
('SEK', 'Swedish Krona', 'کرۆنای سوید', 'كرونة سويدية', 'kr', 0.096000, 124.800000),
('CHF', 'Swiss Franc', 'فرانکی سویسرا', 'فرنك سويسري', 'CHF', 1.140000, 1482.000000),
('SRD', 'Surinamese Dollar', 'دۆلاری سورینام', 'دولار سورينامي', '$', 0.028000, 36.400000),
('SYP', 'Syrian Pound', 'پاوەندی سووریا', 'ليرة سورية', '£', 0.000080, 0.104000),
('TWD', 'New Taiwan Dollar', 'دۆلاری تایوان', 'دولار تايواني', 'NT$', 0.032000, 41.600000),
('THB', 'Thai Baht', 'باتی تایلەند', 'بات تايلندي', '฿', 0.030000, 39.000000),
('TTD', 'Trinidad and Tobago Dollar', 'دۆلاری ترینیداد و تۆباگۆ', 'دولار ترينيداد وتوباغو', 'TT$', 0.148000, 192.400000),
('TRY', 'Turkish Lira', 'لیرای تورکیا', 'ليرة تركية', '₺', 0.037000, 48.100000),
('TVD', 'Tuvaluan Dollar', 'دۆلاری توڤالوو', 'دولار توفالو', '$', 0.680000, 884.000000),
('UAH', 'Ukrainian Hryvnia', 'هریڤنای ئۆکرانیا', 'هريفنيا أوكرانية', '₴', 0.027000, 35.100000),
('UYU', 'Uruguayan Peso', 'پێسۆی ئوروگوای', 'بيزو أوروغواي', '$U', 0.025000, 32.500000),
('UZS', 'Uzbekistan Som', 'سۆمی ئوزبەکستان', 'سوم أوزبكستاني', 'лв', 0.000088, 0.114400),
('VEF', 'Venezuelan Bolívar', 'بۆلیڤاری ڤێنێزوێلا', 'بوليفار فنزويلي', 'Bs', 0.000003, 0.003900),
('VND', 'Vietnamese Dong', 'دۆنگی ڤیەتنام', 'دونغ فيتنامي', '₫', 0.000041, 0.053300),
('YER', 'Yemeni Rial', 'ڕیاڵی یەمەن', 'ريال يمني', '﷼', 0.004000, 5.200000),
('ZWD', 'Zimbabwean Dollar', 'دۆلاری زیمبابوی', 'دولار زيمبابوي', 'Z$', 0.003100, 4.030000);

-- --------------------------------------------------------

-- Table structure for table `offices`
CREATE TABLE IF NOT EXISTS `offices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `code` varchar(50) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` text,
  `contact_person` varchar(100) DEFAULT NULL,
  `commission_rate` decimal(5,2) DEFAULT 0.00,
  `balance_iqd` decimal(15,2) DEFAULT 0.00,
  `balance_usd` decimal(15,2) DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 1,
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `transaction_owners`
CREATE TABLE IF NOT EXISTS `transaction_owners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `id_number` varchar(50) DEFAULT NULL,
  `address` text,
  `balance_iqd` decimal(15,2) DEFAULT 0.00,
  `balance_usd` decimal(15,2) DEFAULT 0.00,
  `total_sent` decimal(15,2) DEFAULT 0.00,
  `total_received` decimal(15,2) DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 1,
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `phone` (`phone`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `owner_safe_transactions`
CREATE TABLE IF NOT EXISTS `owner_safe_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) NOT NULL,
  `transaction_type` enum('deposit','withdrawal','transfer_fee','commission') NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `currency_code` varchar(10) NOT NULL,
  `amount_iqd` decimal(15,2) NOT NULL,
  `balance_before` decimal(15,2) DEFAULT 0.00,
  `balance_after` decimal(15,2) DEFAULT 0.00,
  `reference_id` int(11) DEFAULT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `description` text,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `owner_id` (`owner_id`),
  KEY `transaction_type` (`transaction_type`),
  KEY `currency_code` (`currency_code`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `owner_safe_transactions_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `transaction_owners` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `expenses`
CREATE TABLE IF NOT EXISTS `expenses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(100) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `currency_code` varchar(10) NOT NULL DEFAULT 'IQD',
  `amount_iqd` decimal(15,2) NOT NULL,
  `description` text,
  `expense_date` date NOT NULL,
  `receipt_number` varchar(50) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category` (`category`),
  KEY `expense_date` (`expense_date`),
  KEY `currency_code` (`currency_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `users`
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `pin` varchar(255) NOT NULL,
  `full_name` varchar(200) NOT NULL,
  `role` enum('admin','manager','user') NOT NULL DEFAULT 'user',
  `permissions` text,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (PIN: 123456)
INSERT INTO `users` (`username`, `pin`, `full_name`, `role`, `permissions`, `is_active`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin', '{"all":true}', 1);

-- --------------------------------------------------------

-- Table structure for table `transfers`
CREATE TABLE IF NOT EXISTS `transfers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `receipt_number` varchar(50) NOT NULL,
  `transfer_type` enum('incoming','outgoing') NOT NULL,
  `sender_name` varchar(200) NOT NULL,
  `sender_phone` varchar(50) DEFAULT NULL,
  `receiver_name` varchar(200) NOT NULL,
  `receiver_phone` varchar(50) DEFAULT NULL,
  `office_id` int(11) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `currency_code` varchar(10) NOT NULL,
  `amount_iqd` decimal(15,2) NOT NULL,
  `payment_method` enum('cash','debt') NOT NULL DEFAULT 'cash',
  `commission_rate` decimal(5,2) DEFAULT 0.00,
  `commission_amount` decimal(15,2) DEFAULT 0.00,
  `receiver_commission` decimal(15,2) DEFAULT 0.00,
  `office_commission` decimal(15,2) DEFAULT 0.00,
  `net_amount` decimal(15,2) DEFAULT 0.00,
  `exchange_rate` decimal(15,6) DEFAULT 1.000000,
  `status` enum('pending','completed','cancelled') NOT NULL DEFAULT 'pending',
  `transfer_date` date NOT NULL,
  `completed_date` datetime DEFAULT NULL,
  `notes` text,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `receipt_number` (`receipt_number`),
  KEY `transfer_type` (`transfer_type`),
  KEY `office_id` (`office_id`),
  KEY `currency_code` (`currency_code`),
  KEY `status` (`status`),
  KEY `transfer_date` (`transfer_date`),
  CONSTRAINT `transfers_ibfk_1` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `currency_exchange_log`
CREATE TABLE IF NOT EXISTS `currency_exchange_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_date` date NOT NULL,
  `from_currency` varchar(10) NOT NULL,
  `to_currency` varchar(10) NOT NULL,
  `from_amount` decimal(15,2) NOT NULL,
  `to_amount` decimal(15,2) NOT NULL,
  `exchange_rate` decimal(15,6) NOT NULL,
  `customer_name` varchar(200) DEFAULT NULL,
  `profit_loss` decimal(15,2) DEFAULT 0.00,
  `notes` text,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `transaction_date` (`transaction_date`),
  KEY `from_currency` (`from_currency`),
  KEY `to_currency` (`to_currency`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `cash_transactions`
CREATE TABLE IF NOT EXISTS `cash_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_type` enum('deposit','withdrawal') NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `currency_code` varchar(10) NOT NULL DEFAULT 'IQD',
  `amount_iqd` decimal(15,2) NOT NULL,
  `safe_balance_before` decimal(15,2) DEFAULT 0.00,
  `safe_balance_after` decimal(15,2) DEFAULT 0.00,
  `description` text,
  `transaction_date` date NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `transaction_type` (`transaction_type`),
  KEY `transaction_date` (`transaction_date`),
  KEY `currency_code` (`currency_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `system_settings`
CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings
INSERT INTO `system_settings` (`setting_key`, `setting_value`) VALUES
('company_name', 'Diamond Money Exchange'),
('company_name_ku', 'گۆڕینی پارەی دایمۆند'),
('company_name_ar', 'صرافة الماس'),
('default_language', 'ku'),
('default_commission_rate', '2.5'),
('safe_balance_iqd', '0.00'),
('safe_balance_usd', '0.00'),
('usd_to_iqd_rate', '1300.00'),
('enable_notifications', '1'),
('currency_precision', '2');

-- --------------------------------------------------------

-- Table structure for table `activity_log`
CREATE TABLE IF NOT EXISTS `activity_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `description` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `action` (`action`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
