-- phpMyAdmin SQL Dump
-- version 6.0.0-dev+20260423.63883d5432
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 09, 2026 at 06:25 PM
-- Server version: 8.4.3
-- PHP Version: 8.4.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `britechjr`
--

-- --------------------------------------------------------

--
-- Table structure for table `caja`
--

CREATE TABLE `caja` (
  `caja_id` int NOT NULL,
  `numero` int DEFAULT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `efectivo` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `caja`
--

INSERT INTO `caja` (`caja_id`, `numero`, `nombre`, `efectivo`) VALUES
(1, 1, 'Caja Principal', 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `categoria`
--

CREATE TABLE `categoria` (
  `categoria_id` int NOT NULL,
  `nombre` varchar(50) DEFAULT NULL,
  `ubicacion` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `categoria`
--

INSERT INTO `categoria` (`categoria_id`, `nombre`, `ubicacion`) VALUES
(1, 'Tecnologia', 'General'),
(2, 'Auriculares', '');

-- --------------------------------------------------------

--
-- Table structure for table `cliente`
--

CREATE TABLE `cliente` (
  `cliente_id` int NOT NULL,
  `usuario_id` int DEFAULT NULL,
  `tipo_cliente` enum('minorista','mayorista') DEFAULT 'minorista',
  `tipo_documento` varchar(20) DEFAULT NULL,
  `numero_documento` varchar(30) DEFAULT NULL,
  `provincia` varchar(50) DEFAULT NULL,
  `ciudad` varchar(50) DEFAULT NULL,
  `direccion` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `cliente`
--

INSERT INTO `cliente` (`cliente_id`, `usuario_id`, `tipo_cliente`, `tipo_documento`, `numero_documento`, `provincia`, `ciudad`, `direccion`, `telefono`) VALUES
(1, NULL, 'minorista', 'DNI', '00000000', 'N/A', 'N/A', 'N/A', 'N/A'),
(2, 2, 'minorista', 'DNI', '', '', '', '', ''),
(3, 8, 'minorista', NULL, NULL, NULL, NULL, NULL, NULL),
(4, 9, 'minorista', NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `combo`
--

CREATE TABLE `combo` (
  `combo_id` int NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `combo_detalle`
--

CREATE TABLE `combo_detalle` (
  `combo_detalle_id` int NOT NULL,
  `combo_id` int DEFAULT NULL,
  `producto_id` int DEFAULT NULL,
  `cantidad` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `envio`
--

CREATE TABLE `envio` (
  `envio_id` int NOT NULL,
  `pedido_id` int DEFAULT NULL,
  `direccion` varchar(200) DEFAULT NULL,
  `empresa` varchar(100) DEFAULT NULL,
  `tracking` varchar(100) DEFAULT NULL,
  `estado` enum('pendiente','enviado','entregado') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventario`
--

CREATE TABLE `inventario` (
  `inventario_id` int NOT NULL,
  `producto_id` int DEFAULT NULL,
  `stock_actual` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `inventario`
--

INSERT INTO `inventario` (`inventario_id`, `producto_id`, `stock_actual`) VALUES
(1, 1, 100);

-- --------------------------------------------------------

--
-- Table structure for table `movimiento_inventario`
--

CREATE TABLE `movimiento_inventario` (
  `movimiento_id` int NOT NULL,
  `producto_id` int DEFAULT NULL,
  `tipo` enum('entrada','salida') DEFAULT NULL,
  `cantidad` int DEFAULT NULL,
  `origen` varchar(50) DEFAULT NULL,
  `fecha` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `movimiento_inventario`
--

INSERT INTO `movimiento_inventario` (`movimiento_id`, `producto_id`, `tipo`, `cantidad`, `origen`, `fecha`) VALUES
(1, 1, 'entrada', 100, 'stock inicial', '2026-04-30 17:59:18');

-- --------------------------------------------------------

--
-- Table structure for table `notificacion`
--

CREATE TABLE `notificacion` (
  `notificacion_id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `mensaje` text NOT NULL,
  `tipo` enum('info','advertencia','error','exito') DEFAULT 'info',
  `leida` tinyint(1) DEFAULT '0',
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pago`
--

CREATE TABLE `pago` (
  `pago_id` int NOT NULL,
  `pedido_id` int DEFAULT NULL,
  `metodo` varchar(50) DEFAULT NULL,
  `estado` enum('pendiente','aprobado','rechazado') DEFAULT NULL,
  `fecha` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pedido`
--

CREATE TABLE `pedido` (
  `pedido_id` int NOT NULL,
  `usuario_id` int DEFAULT NULL,
  `estado` enum('pendiente','pagado','enviado','entregado','cancelado') DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `fecha` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pedido_detalle`
--

CREATE TABLE `pedido_detalle` (
  `pedido_detalle_id` int NOT NULL,
  `pedido_id` int DEFAULT NULL,
  `producto_id` int DEFAULT NULL,
  `cantidad` int DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `precio`
--

CREATE TABLE `precio` (
  `precio_id` int NOT NULL,
  `producto_id` int DEFAULT NULL,
  `tipo_precio` enum('minorista','mayorista') DEFAULT NULL,
  `cantidad_minima` int DEFAULT '1',
  `precio` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `precio`
--

INSERT INTO `precio` (`precio_id`, `producto_id`, `tipo_precio`, `cantidad_minima`, `precio`) VALUES
(1, 1, 'minorista', 1, 25000.00),
(2, 1, 'mayorista', 1, 17000.00),
(3, 1, 'mayorista', 10, 15000.00);

-- --------------------------------------------------------

--
-- Table structure for table `producto`
--

CREATE TABLE `producto` (
  `producto_id` int NOT NULL,
  `codigo` varchar(80) DEFAULT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `tipo` enum('stock','pedido') DEFAULT 'stock',
  `marca` varchar(50) DEFAULT NULL,
  `modelo` varchar(50) DEFAULT NULL,
  `estado` varchar(20) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `precio_base` decimal(10,2) DEFAULT NULL,
  `categoria_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `producto`
--

INSERT INTO `producto` (`producto_id`, `codigo`, `nombre`, `tipo`, `marca`, `modelo`, `estado`, `foto`, `precio_base`, `categoria_id`) VALUES
(1, 'AUR-001', 'Auricular Bluetooth', 'stock', 'Genérica', 'X1', 'activo', NULL, 25000.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `usuario`
--

CREATE TABLE `usuario` (
  `usuario_id` int NOT NULL,
  `usuario_nombre` varchar(50) DEFAULT NULL,
  `usuario_apellido` varchar(50) DEFAULT NULL,
  `usuario_email` varchar(100) DEFAULT NULL,
  `usuario_usuario` varchar(50) DEFAULT NULL,
  `usuario_clave` varchar(255) DEFAULT NULL,
  `rol` enum('admin','vendedor') DEFAULT 'vendedor',
  `email_verificado` tinyint(1) DEFAULT '0',
  `token_verificacion` varchar(100) DEFAULT NULL,
  `token_expiracion` datetime DEFAULT NULL,
  `token_reset` varchar(64) DEFAULT NULL,
  `token_reset_expiracion` datetime DEFAULT NULL,
  `usuario_foto` varchar(200) DEFAULT NULL,
  `caja_id` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `usuario`
--

INSERT INTO `usuario` (`usuario_id`, `usuario_nombre`, `usuario_apellido`, `usuario_email`, `usuario_usuario`, `usuario_clave`, `rol`, `email_verificado`, `token_verificacion`, `token_expiracion`, `token_reset`, `token_reset_expiracion`, `usuario_foto`, `caja_id`) VALUES
(1, 'Admin', 'Principal', 'admin@britech.com', 'admin', 'admin123', 'admin', 1, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 'Eric Gonzalo', 'Britez', 'erikbritejr@gmail.com', 'erikbritejr@gmail.com', '$2y$10$Ufn2rhYYQvxJ21PtWXfau.fyA26ec0WP96BBY7eRpfLwlIRCgfqLe', 'vendedor', 1, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'Francoi', 'Garcia', 'francokpo@gmail.com', 'francokpo@gmail.com', 'francomastan', 'vendedor', 0, NULL, NULL, NULL, NULL, '', NULL),
(4, 'Eric', 'Britez', 'britezeric91impar@gmail.com', 'britezeric91impar@gmail.com', '$2y$12$CmoXgl07B0Kfgi216W07GezX/LE5Tj4TE56gJVSWeRHkX4Po3qmnC', 'vendedor', 0, 'c55f53731b7fd37469291de92ab7f7ec8480340851c9ac1b071204ceb047ae53', '2026-05-03 17:43:43', NULL, NULL, '', NULL),
(5, 'Franco', 'Jojot', 'jojotfranco5@gmail.com', 'jojotfranco5@gmail.com', '$2y$12$E2xrwx2xtpRKITwrBF.t5OibHjGLOIYQ6No2NLvgmno2ABwnslvzW', 'vendedor', 1, NULL, NULL, NULL, NULL, '', NULL),
(6, 'Tobias', 'Soto', 'sototobias855@gmail.com', 'sototobias855@gmail.com', '$2y$12$UQ5NBxO.q6jdNpG.en.15eDlXk3KGg.dVC6Q8qfvn574TDAxIyIBW', 'vendedor', 1, NULL, NULL, NULL, NULL, '', NULL),
(7, 'Eric', 'Britech', 'britechfsa@gmail.com', 'britechfsa@gmail.com', '$2y$10$IxLFs0GeRlZxD.AaSIZOsOJ6BRM9aIwBji1vhww0rAX3FcHcNnUnu', 'vendedor', 1, NULL, NULL, NULL, NULL, '', NULL),
(8, 'Erc', 'Briites', 'erikbrite@gmail.com', 'erikbrite@gmail.com', '$2y$12$NEsMVXAIW6SwcDN7TsMCMeLJ7lMYRDT/NJa2b/TzA20zOZMSh88/S', 'vendedor', 0, NULL, NULL, NULL, NULL, '', NULL),
(9, 'Tomas', 'Fariña', 'farinatomas91@gmail.com', 'farinatomas91@gmail.com', '$2y$12$OHxnGUjzdSt43Uk07QMSc.nDLjqfzEgeO4Du.mN9by7l99CnY4Cse', 'vendedor', 0, 'd70a1592e3e95314f457fb736c879d1567513e5e5d27095550be25d4e0c404ba', '2026-06-08 18:29:41', NULL, NULL, '', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `venta`
--

CREATE TABLE `venta` (
  `venta_id` int NOT NULL,
  `codigo` varchar(100) DEFAULT NULL,
  `fecha` datetime DEFAULT CURRENT_TIMESTAMP,
  `total` decimal(10,2) DEFAULT NULL,
  `pagado` decimal(10,2) DEFAULT NULL,
  `cambio` decimal(10,2) DEFAULT NULL,
  `usuario_id` int DEFAULT NULL,
  `cliente_id` int DEFAULT NULL,
  `caja_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `venta_detalle`
--

CREATE TABLE `venta_detalle` (
  `venta_detalle_id` int NOT NULL,
  `venta_id` int DEFAULT NULL,
  `producto_id` int DEFAULT NULL,
  `cantidad` int DEFAULT NULL,
  `precio_unitario` decimal(10,2) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `descripcion` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `caja`
--
ALTER TABLE `caja`
  ADD PRIMARY KEY (`caja_id`);

--
-- Indexes for table `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`categoria_id`);

--
-- Indexes for table `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`cliente_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indexes for table `combo`
--
ALTER TABLE `combo`
  ADD PRIMARY KEY (`combo_id`);

--
-- Indexes for table `combo_detalle`
--
ALTER TABLE `combo_detalle`
  ADD PRIMARY KEY (`combo_detalle_id`),
  ADD KEY `combo_id` (`combo_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indexes for table `envio`
--
ALTER TABLE `envio`
  ADD PRIMARY KEY (`envio_id`),
  ADD KEY `pedido_id` (`pedido_id`);

--
-- Indexes for table `inventario`
--
ALTER TABLE `inventario`
  ADD PRIMARY KEY (`inventario_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indexes for table `movimiento_inventario`
--
ALTER TABLE `movimiento_inventario`
  ADD PRIMARY KEY (`movimiento_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indexes for table `notificacion`
--
ALTER TABLE `notificacion`
  ADD PRIMARY KEY (`notificacion_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indexes for table `pago`
--
ALTER TABLE `pago`
  ADD PRIMARY KEY (`pago_id`),
  ADD KEY `pedido_id` (`pedido_id`);

--
-- Indexes for table `pedido`
--
ALTER TABLE `pedido`
  ADD PRIMARY KEY (`pedido_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indexes for table `pedido_detalle`
--
ALTER TABLE `pedido_detalle`
  ADD PRIMARY KEY (`pedido_detalle_id`),
  ADD KEY `pedido_id` (`pedido_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indexes for table `precio`
--
ALTER TABLE `precio`
  ADD PRIMARY KEY (`precio_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indexes for table `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`producto_id`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Indexes for table `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`usuario_id`),
  ADD UNIQUE KEY `email` (`usuario_email`);

--
-- Indexes for table `venta`
--
ALTER TABLE `venta`
  ADD PRIMARY KEY (`venta_id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `caja_id` (`caja_id`);

--
-- Indexes for table `venta_detalle`
--
ALTER TABLE `venta_detalle`
  ADD PRIMARY KEY (`venta_detalle_id`),
  ADD KEY `venta_id` (`venta_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `caja`
--
ALTER TABLE `caja`
  MODIFY `caja_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categoria`
--
ALTER TABLE `categoria`
  MODIFY `categoria_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `cliente`
--
ALTER TABLE `cliente`
  MODIFY `cliente_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `combo`
--
ALTER TABLE `combo`
  MODIFY `combo_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `combo_detalle`
--
ALTER TABLE `combo_detalle`
  MODIFY `combo_detalle_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `envio`
--
ALTER TABLE `envio`
  MODIFY `envio_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventario`
--
ALTER TABLE `inventario`
  MODIFY `inventario_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `movimiento_inventario`
--
ALTER TABLE `movimiento_inventario`
  MODIFY `movimiento_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `notificacion`
--
ALTER TABLE `notificacion`
  MODIFY `notificacion_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pago`
--
ALTER TABLE `pago`
  MODIFY `pago_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pedido`
--
ALTER TABLE `pedido`
  MODIFY `pedido_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pedido_detalle`
--
ALTER TABLE `pedido_detalle`
  MODIFY `pedido_detalle_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `precio`
--
ALTER TABLE `precio`
  MODIFY `precio_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `producto`
--
ALTER TABLE `producto`
  MODIFY `producto_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `usuario`
--
ALTER TABLE `usuario`
  MODIFY `usuario_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `venta`
--
ALTER TABLE `venta`
  MODIFY `venta_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `venta_detalle`
--
ALTER TABLE `venta_detalle`
  MODIFY `venta_detalle_id` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cliente`
--
ALTER TABLE `cliente`
  ADD CONSTRAINT `cliente_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`usuario_id`);

--
-- Constraints for table `combo_detalle`
--
ALTER TABLE `combo_detalle`
  ADD CONSTRAINT `combo_detalle_ibfk_1` FOREIGN KEY (`combo_id`) REFERENCES `combo` (`combo_id`),
  ADD CONSTRAINT `combo_detalle_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `producto` (`producto_id`);

--
-- Constraints for table `envio`
--
ALTER TABLE `envio`
  ADD CONSTRAINT `envio_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedido` (`pedido_id`);

--
-- Constraints for table `inventario`
--
ALTER TABLE `inventario`
  ADD CONSTRAINT `inventario_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `producto` (`producto_id`);

--
-- Constraints for table `movimiento_inventario`
--
ALTER TABLE `movimiento_inventario`
  ADD CONSTRAINT `movimiento_inventario_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `producto` (`producto_id`);

--
-- Constraints for table `notificacion`
--
ALTER TABLE `notificacion`
  ADD CONSTRAINT `notificacion_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`usuario_id`);

--
-- Constraints for table `pago`
--
ALTER TABLE `pago`
  ADD CONSTRAINT `pago_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedido` (`pedido_id`);

--
-- Constraints for table `pedido`
--
ALTER TABLE `pedido`
  ADD CONSTRAINT `pedido_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`usuario_id`);

--
-- Constraints for table `pedido_detalle`
--
ALTER TABLE `pedido_detalle`
  ADD CONSTRAINT `pedido_detalle_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedido` (`pedido_id`),
  ADD CONSTRAINT `pedido_detalle_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `producto` (`producto_id`);

--
-- Constraints for table `precio`
--
ALTER TABLE `precio`
  ADD CONSTRAINT `precio_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `producto` (`producto_id`);

--
-- Constraints for table `producto`
--
ALTER TABLE `producto`
  ADD CONSTRAINT `producto_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categoria` (`categoria_id`);

--
-- Constraints for table `venta`
--
ALTER TABLE `venta`
  ADD CONSTRAINT `venta_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`usuario_id`),
  ADD CONSTRAINT `venta_ibfk_2` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`cliente_id`),
  ADD CONSTRAINT `venta_ibfk_3` FOREIGN KEY (`caja_id`) REFERENCES `caja` (`caja_id`);

--
-- Constraints for table `venta_detalle`
--
ALTER TABLE `venta_detalle`
  ADD CONSTRAINT `venta_detalle_ibfk_1` FOREIGN KEY (`venta_id`) REFERENCES `venta` (`venta_id`),
  ADD CONSTRAINT `venta_detalle_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `producto` (`producto_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
