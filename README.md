# magento-cache-buster
Implement cache-busting techniques from https://html5boilerplate.com/

----

Requires changes to your .htaccess,

# If you're not using a build process to manage your filename version revving,
# you might want to consider enabling the following directives to route all
# requests such as `/css/style.12345.css` to `/css/style.css`.

# To understand why this is important and a better idea than `*.css?v231`, read:
# http://www.stevesouders.com/blog/2008/08/23/revving-filenames-dont-use-querystring/

<IfModule mod_rewrite.c>
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^(.+)\.(\d+)\.(css|cur|gif|ico|jpe?g|js|png|svgz?|webp)$ $1.$3 [L]
</IfModule>
