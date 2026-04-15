ALTER TABLE `broadcast_events`
  ADD COLUMN `access_code_hash` VARCHAR(255) NULL AFTER `local_file_path`;
