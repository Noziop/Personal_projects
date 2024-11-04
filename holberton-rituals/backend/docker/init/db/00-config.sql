-- 00-config.sql
-- Balanced configuration for Synology DS923+ Multi-Projects
-- Estimated users: ~75 (70 students + 4 staff)
-- RAM: 20GB | Storage: 4TB | Network: 8Gb/s symmetric

-- =============================
-- Server Configuration
-- =============================

-- Character set and collation
SET GLOBAL character_set_server = 'utf8mb4';
SET GLOBAL collation_server = 'utf8mb4_unicode_ci';

-- Strict SQL mode
SET GLOBAL sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- Memory optimization
-- 80% of available RAM (16GB out of 20GB) pour InnoDB[1]
SET GLOBAL innodb_buffer_pool_size = 16 * 1024 * 1024 * 1024;  
SET GLOBAL innodb_file_per_table = ON;
-- Durabilité maximale pour les transactions[1]
SET GLOBAL innodb_flush_log_at_trx_commit = 1;  

-- IO optimization
-- Valeurs adaptées pour SSD[2]
SET GLOBAL innodb_io_capacity = 2000;      -- Base IO capacity
SET GLOBAL innodb_io_capacity_max = 4000;  -- Max IO capacity
-- SET GLOBAL innodb_read_io_threads = 8;     -- Optimisé pour les lectures
-- ET GLOBAL innodb_write_io_threads = 8;    -- Optimisé pour les écritures

-- Connection optimization
SET GLOBAL max_connections = 200;           -- ~2.5x nombre d'utilisateurs[3]
SET GLOBAL thread_cache_size = 20;          -- 10% de max_connections[3]
-- SET GLOBAL max_allowed_packet = 64M;        -- Pour les grandes transactions

-- Timeout configuration
SET GLOBAL wait_timeout = 600;              -- 10 minutes, plus strict[2]
SET GLOBAL interactive_timeout = 600;

-- Query Cache (désactivé car peu efficace sur MariaDB 10.5+)[1]
SET GLOBAL query_cache_type = 0;
SET GLOBAL query_cache_size = 0;

-- =============================
-- Performance Monitoring
-- =============================

-- Slow Query Logging
SET GLOBAL slow_query_log = ON;
SET GLOBAL long_query_time = 1;            -- Log queries > 1 second[2]
SET GLOBAL log_output = 'FILE';
SET GLOBAL slow_query_log_file = '/var/log/mysql/mysql-slow.log';

-- =============================
-- Database Creation
-- =============================

CREATE DATABASE IF NOT EXISTS holberton_rituals
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE holberton_rituals;

-- =============================
-- Session Parameters
-- =============================

SET SESSION sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
SET SESSION time_zone = '+00:00';

-- =============================
-- Version Comments
-- =============================

/*
Configuration notes:
- Optimized for multi-project environment
- Moderate memory usage (20% RAM)
- Balanced IO threads
- Settings adapted for resource sharing
- UTC timezone for consistency
- Initial configuration for ~75 users
- Minimal logging for startup
- To be monitored and adjusted based on:
  * Actual usage patterns
  * User feedback
  * Performance metrics
  * Consistency with FastAPI/Vue logs

Version: 1.0.0
Description: Initial configuration for Holberton Rituals
Made with Love & passion by: Fassih & Zoé
Date: 2024-02-11

Notes:
- Optimized for MariaDB 10.5+
- Configuration adapted for containerized environment
- Parameters adjustable according to server load
*/