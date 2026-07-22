-- Default Pricing Template Seed Data
-- This seeds a reference config that new users can copy on signup.
-- The config has no user_id (user_id = NULL) — Backend Agent copies it when a user picks "Start with default template".

INSERT INTO pricing_configs (id, user_id, name, currency, base_rate, is_default)
VALUES (1, NULL, 'Default Template', '₦', 20000.00, 1);

-- Complexity Tiers (Step 2 of wizard)
INSERT INTO complexity_tiers (pricing_config_id, name, multiplier, sort_order) VALUES
(1, 'Simple', 1.0, 1),
(1, 'Medium', 2.0, 2),
(1, 'Complex', 3.5, 3);

-- Screen Templates (Step 3 of wizard) — referenced by name against the tiers above
INSERT INTO screen_templates (pricing_config_id, name, complexity_tier_id, sort_order) VALUES
(1, 'Login / Sign Up',    (SELECT id FROM complexity_tiers WHERE pricing_config_id = 1 AND name = 'Simple'),  1),
(1, 'Dashboard / Home',   (SELECT id FROM complexity_tiers WHERE pricing_config_id = 1 AND name = 'Medium'),  2),
(1, 'User Profile',       (SELECT id FROM complexity_tiers WHERE pricing_config_id = 1 AND name = 'Simple'),  3),
(1, 'Settings',           (SELECT id FROM complexity_tiers WHERE pricing_config_id = 1 AND name = 'Simple'),  4),
(1, 'Search & Filters',   (SELECT id FROM complexity_tiers WHERE pricing_config_id = 1 AND name = 'Medium'),  5),
(1, 'Checkout / Payment', (SELECT id FROM complexity_tiers WHERE pricing_config_id = 1 AND name = 'Complex'), 6);

-- Integrations (Step 3 of wizard)
INSERT INTO integrations (pricing_config_id, name, points, sort_order) VALUES
(1, 'Payment Gateway', 3, 1),
(1, 'SMS / Email Notifications', 2, 2),
(1, 'Maps / Location', 3, 3),
(1, 'Social Login', 1, 4),
(1, 'Analytics', 1, 5),
(1, 'Custom API Integration', 4, 6);

-- Platform Multipliers (Step 4 of wizard)
INSERT INTO platform_multipliers (pricing_config_id, platform, multiplier) VALUES
(1, 'Web', 1.0),
(1, 'Android', 1.3),
(1, 'iOS', 1.3),
(1, 'Desktop', 1.2),
(1, 'PWA', 1.1);

-- Package Tiers (Step 6 of wizard)
INSERT INTO package_tiers (pricing_config_id, name, multiplier, sort_order) VALUES
(1, 'Basic', 1.0, 1),
(1, 'Professional', 1.5, 2),
(1, 'Premium', 2.5, 3);

-- Maintenance Settings (Step 5 of wizard)
INSERT INTO maintenance_settings (pricing_config_id, model, value) VALUES
(1, 'percentage', 15.00);
