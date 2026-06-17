-- Seed script to populate the DevBlog application with high-quality content and test user

USE `blog`;

-- Clear existing data for a clean seed state
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE `users`;
TRUNCATE TABLE `posts`;
SET FOREIGN_KEY_CHECKS = 1;

-- Insert seed user (Username: developer, Password: devpass123)
INSERT INTO `users` (`username`, `password`) VALUES 
('developer', '$2y$10$LUTONQM6rNT2encfpjBXluUsO/noygxq7MhmQoKxe9M/g8gW8KWJ2');

-- Insert high-quality developer blog posts (no placeholder text)
INSERT INTO `posts` (`title`, `content`, `created_at`) VALUES 
(
  'Demystifying CSS Grid & Flexbox in Modern Layouts', 
  'For years, web developers struggled with float properties and positioning rules to construct multi-column web structures. Modern CSS has shifted this paradigm entirely with CSS Flexbox and CSS Grid. \n\nFlexbox is ideal for one-dimensional layouts (a single row or column), giving items the flexibility to expand and shrink relative to parent spaces. Grid, conversely, excels at two-dimensional structures, allowing you to control columns and rows simultaneously. By nesting Flexbox components within CSS Grid layouts, developers can build complex, fluid responsive designs with minimal CSS rules.',
  DATE_SUB(NOW(), INTERVAL 3 HOUR)
),
(
  'Mastering SQL Injection Prevention via PHP PDO', 
  'SQL Injection (SQLi) remains one of the most critical vulnerabilities in database-backed applications. It occurs when untrusted input is directly concatenated into a SQL statement, allowing attackers to manipulate queries. \n\nIn PHP, the most effective defense is utilizing PHP Data Objects (PDO) with prepared statements. Prepared statements separate query logic from raw parameter values. When parameters are sent to the MySQL server, they are treated as pure literals, completely neutralizing potential injection vectors. Avoid interpolating PHP variables directly in SQL strings; always use PDO placeholers (:name or ?).',
  DATE_SUB(NOW(), INTERVAL 12 HOUR)
),
(
  'The Architecture of Secure Web Sessions', 
  'Session hijacking and session fixation pose constant threats to web services. Securing user authentication states requires strict configuration rules in your php.ini or session parameters. \n\nAlways ensure that `session.cookie_httponly` is enabled—this blocks client-side scripts from reading the session cookie, safeguarding it from XSS extraction. Additionally, enforce the `SameSite=Lax` cookie parameter to block cross-site request forgery vectors, and mandate secure session cookie tags so credentials are encrypted over HTTPS transport lines.',
  DATE_SUB(NOW(), INTERVAL 1 DAY)
),
(
  'Understanding Cross-Site Request Forgery (CSRF)', 
  'Cross-Site Request Forgery is an attack vector where a malicious site triggers an unintended action on a web service where a user is currently authenticated. Since the user\'s browser automatically attaches active session cookies to all requests made to that domain, the target server processes the action as legitimate. \n\nTo combat this, professional applications use cryptographically secure CSRF tokens stored in sessions and matched against hidden fields on POST requests. Without knowledge of this random token, malicious sites cannot construct valid request payloads, protecting destructive endpoints from exploitation.',
  DATE_SUB(NOW(), INTERVAL 2 DAY)
);
