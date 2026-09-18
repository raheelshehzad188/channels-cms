ALTER TABLE users
    ADD COLUMN commission_percent DECIMAL(8,2) NOT NULL DEFAULT 0.00 AFTER commission;
