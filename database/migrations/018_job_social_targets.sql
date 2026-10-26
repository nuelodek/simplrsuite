ALTER TABLE job_posts
  ADD COLUMN social_targets VARCHAR(255) NOT NULL DEFAULT '' AFTER status;
