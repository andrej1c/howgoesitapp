# How Goes It? App.
## Requirements
* Create pages with urls: **"my-account", "register", "following", "followers", "score"**. Must have these slugs. Can be changed in includes/class-how-goes-it.php in define_urls() function.

## Shortcodes
There is currently 6 shortcodes:
* **[hgi_login_form]** - Shows login form, put on "login" page.
* **[hgi_score_form]** - Shows form for setting new score. Put on "Score" page.
* **[hgi_my_last_score]** - Shows last score for the user and timestamp. Use on "Score" page.
* **[hgi_register_form]** - Shows register form, put on "register" page.
* **[hgi_followers_or_code]** - Shows table of followers for the user, also will switch to code generator using GET attribute. Use on "Followers" page.
* **[hgi_users_for_follower]** - Shows table for follower, which displays people where he is connected to. Use on "Following" page.
