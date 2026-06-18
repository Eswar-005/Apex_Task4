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
  'SQL Injection (SQLi) remains one of the most critical vulnerabilities in database-backed applications. It occurs when untrusted input is directly concatenated into a SQL statement, allowing attackers to manipulate queries. \n\nIn PHP, the most effective defense is utilizing PHP Data Objects (PDO) with prepared statements. Prepared statements separate query logic from raw parameter values. When parameters are sent to the MySQL server, they are treated as pure literals, completely neutralizing potential injection vectors. Avoid interpolating PHP variables directly in SQL strings; always use PDO placeholders (:name or ?).',
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
),
(
  'Mitigating Cross-Site Scripting (XSS) in PHP',
  'Cross-Site Scripting (XSS) vulnerabilities occur when application data flows from untrusted inputs to the browser without sufficient escaping. Attackers exploit XSS to run malicious scripts in victims browsers, stealing cookies or hijacking active sessions. \n\nIn PHP, the primary defense is escaping HTML characters outputted to templates. The `htmlspecialchars()` helper handles converting markup symbols (like <, >, &) into neutral entities. Always pair escaping with strict Content Security Policies (CSP) to restrict sources of execution.',
  DATE_SUB(NOW(), INTERVAL 3 DAY)
),
(
  'The Power of Content Security Policy (CSP)',
  'A Content Security Policy (CSP) is an HTTP response header that helps mitigate XSS and clickjacking attacks. By declaring approved sources of content—such as scripts, styles, images, and fonts—that the browser is allowed to load, you significantly reduce execution risks. \n\nEnforcing a policy like "default-src self" prevents browsers from loading scripts from unapproved external domains, rendering XSS injection points non-executable even if markup output escaping fails.',
  DATE_SUB(NOW(), INTERVAL 4 DAY)
),
(
  'A Guide to PHP Password Hashing Best Practices',
  'Storing passwords in plain text or using weak hashing schemes (like MD5 or SHA1) leaves accounts vulnerable in database breaches. PHP provides a native standard API for robust credential protection through `password_hash()`. \n\nUsing the default algorithm `PASSWORD_DEFAULT` utilizes bcrypt, which introduces high CPU costs (work factor) to slow down brute force cracking. Paired with `password_verify()`, the system compares inputs securely, handling key salts automatically.',
  DATE_SUB(NOW(), INTERVAL 5 DAY)
),
(
  'Managing Secrets and Environment Variables Safely',
  'Hardcoding API keys, database credentials, or secret configuration details inside your source files exposes critical assets when codebases are shared or committed. Secure practices mandate loading settings through environment variables (.env files). \n\nIn PHP, packages like `phpdotenv` pull parameters from safe root configurations and store them in the `$_ENV` global space. Expose configuration values to endpoints programmatically, and restrict .env access inside production servers.',
  DATE_SUB(NOW(), INTERVAL 6 DAY)
),
(
  'Implementing Rate Limiting in Web Applications',
  'Brute force attacks, web scraping, and denial-of-service attempts degrade resources and compromise accounts. Rate limiting restricts the frequency of requests a client can dispatch to critical routes within window parameters. \n\nUtilizing key-value cache services like Redis to track request count per IP addresses is the industry standard. Implement token bucket algorithms to handle transient burst traffic smoothly.',
  DATE_SUB(NOW(), INTERVAL 7 DAY)
),
(
  'Modern Web Storage: LocalStorage vs HTTP-Only Cookies',
  'Where to store JSON Web Tokens (JWT) or authentication state remains a highly debated frontend topic. LocalStorage provides easy client-side API access, but is vulnerable to Cross-Site Scripting (XSS) extraction. \n\nHTTP-Only cookies, conversely, cannot be accessed via frontend JavaScript, preventing script-based token theft. While cookie routing requires CSRF protections, it represents the standard choice for highly sensitive sessions.',
  DATE_SUB(NOW(), INTERVAL 8 DAY)
);
