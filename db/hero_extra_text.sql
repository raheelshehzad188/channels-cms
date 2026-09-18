ALTER TABLE store_hero_slides
  ADD COLUMN extra_text TINYINT(1) NOT NULL DEFAULT 1 AFTER disc_on;

ALTER TABLE categories
  ADD COLUMN hero_extra_text TINYINT(1) NOT NULL DEFAULT 1 AFTER hero_disc_on;

ALTER TABLE store_category_settings
  ADD COLUMN hero_extra_text TINYINT(1) NOT NULL DEFAULT 1 AFTER hero_disc_on;
