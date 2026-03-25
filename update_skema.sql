INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('active_scheme', '1'),
('scheme_daily_quota', '150'),
('scheme_daily_start', '08:00'),
('scheme_daily_end', '16:00'),
('scheme_total_quota', '500'),
('scheme_period_start', '2026-03-01'),
('scheme_period_end', '2026-03-03')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
