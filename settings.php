<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Profile - CC Blogs</title>
        <link rel="stylesheet" href="css/styles.css">
        <link rel="stylesheet" href="css/favourites.css">
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <style>
            body {
                font-family: 'Georgia', serif;
                background-color: #f8f8fc;
                color: #333;
                transition: background-color 0.3s ease, color 0.3s ease;
            }
            .dark-mode {
                background-color: #222;
                color: #eee;
            }
            .dark-mode a {
                color: rgb(0, 128, 255);
            }
            #dark-mode-toggle {
                padding: 8px 16px;
                font-size: 16px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                margin: 20px;
                background-color: #eee;
                transition: background-color 0.3s ease;
            }
            #dark-mode-toggle:hover {
                background-color: #ccc;
            }
        </style>
        <style>
            #user-settings-form {
                max-width: 600px;
                margin: 0 auto;
                padding: 20px;
                background: #fff;
                border: 1px solid #ddd;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            #user-settings-form label {
                display: block;
                margin-bottom: 5px;
                font-weight: bold;
                color: #333;
            }
            #user-settings-form input[type="text"],
            #user-settings-form input[type="email"],
            #user-settings-form input[type="password"],
            #user-settings-form textarea {
                width: 100%;
                padding: 10px;
                margin-bottom: 15px;
                border: 1px solid #ccc;
                border-radius: 4px;
                box-sizing: border-box;
                font-size: 1rem;
            }
            #user-settings-form input[type="submit"] {
                background: #2e2f59;
                color: #fff;
                padding: 10px 20px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 1rem;
                transition: background 0.3s ease;
            }
            #user-settings-form input[type="submit"]:hover {
                background: #1a1a3c;
            }
            #user-settings-form input[type="submit"],
            #user-settings-form button {
                background: #2e2f59;
                color: #fff;
                padding: 10px 20px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 1rem;
                transition: background 0.3s ease;
            }
            #user-settings-form input[type="submit"]:hover,
            #user-settings-form button:hover {
                background: #1a1a3c;
            }
        </style>
    </head>
    <body>
        <div id="header"></div>
        
        <div class="profile-container">
            <div class="breadcrumb-filter">
                <div class="breadcrumb"><a href="index.html">Home</a> > Settings</div>
            </div>

            <div class="tabs">
                <button class="tab-link" data-tab="Customization">Customization</button>
                <button class="tab-link active" data-tab="User">User</button>
                <div class="slider"></div>
            </div>

            <!-- Hide the Customization tab and show the User tab by default -->
            <div class="tab-content" id="Customization" style="display: none;">
                <button id="dark-mode-toggle">Toggle Dark Mode</button>
            </div>

            <div class="tab-content" id="User" style="display: block;">
                <form id="user-settings-form" action="php_func/editProfile.php" method="post">
                    <label for="username">Name</label>
                    <input type="text" id="username" name="username" placeholder="Enter your username">
                    
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email">
                    
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter new password">
                    
                    <label for="confirm-password">Confirm Password</label>
                    <input type="password" id="confirm-password" name="confirm-password" placeholder="Confirm password">
                    
                    <label for="bio">Bio</label>
                    <textarea id="bio" name="bio" rows="4" placeholder="Tell us about yourself"></textarea>
                    
                    <label for="profile-picture">Profile Picture URL</label>
                    <input type="file" id="profile-picture" name="profile-picture" placeholder="Link to your profile picture">
                    
                    <input type="submit" value="Save Changes">
                </form>

                <form id="user-settings-form" action="php_func/logout.php" method="post" style="margin-top: 20px;">
                    <button type="submit">Log-out</button>
                </form>
            </div>
        </div>

        <script>
            $(document).ready(function () {
                $("#header").load("header.php");

                //i9nitialize tab slider functionalit
                const $slider = $(".slider");
                const $tabLinks = $(".tab-link");

                function moveSlider($btn) {
                    const leftPos = $btn.position().left;
                    const width = $btn.outerWidth();
                    $slider.css({ left: leftPos, width: width });
                }

                moveSlider($(".tab-link.active"));

                $tabLinks.click(function () {
                    $tabLinks.removeClass("active");
                    $(this).addClass("active");

                    const tab = $(this).data("tab");
                    $(".tab-content:visible").slideUp(function () {
                        $("#" + tab).slideDown();
                    });

                    moveSlider($(this));
                });
            });
        </script>
        <script>
            const toggleButton = document.getElementById('dark-mode-toggle');
            toggleButton.addEventListener('click', function(){
                if (localStorage.getItem('theme') === 'dark') {
                    localStorage.setItem('theme', 'light');
                    var darkModeLink = document.getElementById('dark-mode-css');
                    if(darkModeLink) darkModeLink.remove();
                } else {
                    localStorage.setItem('theme', 'dark');
                    var darkModeLink = document.createElement('link');
                    darkModeLink.rel = 'stylesheet';
                    darkModeLink.href = 'css/darkmode.css';
                    darkModeLink.id = 'dark-mode-css';
                    document.head.appendChild(darkModeLink);
                }
            });
        </script>
        <script>
            $(document).ready(function () {
                $("#header").load("header.php");

                const $form = $("#user-settings-form").first(); //target only the update form

                $form.submit(function (event) {
                    event.preventDefault(); //always prevent first

                    let isValid = true;

                    //validate username (optional)
                    const username = $("#username").val().trim();
                    if (username !== "" && username.length < 2) {
                        isValid = false;
                        $("#username").addClass("error").next(".error-message").show();
                    } else {
                        $("#username").removeClass("error").next(".error-message").hide();
                    }

                    //Validate email (optional)
                    const email = $("#email").val().trim();
                    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (email !== "" && !emailPattern.test(email)) {
                        isValid = false;
                        $("#email").addClass("error").next(".error-message").show();
                    } else {
                        $("#email").removeClass("error").next(".error-message").hide();
                    }

                    //Validate password (optional)
                    const password = $("#password").val().trim();
                    const confirmPassword = $("#confirm-password").val().trim();
                    if (password !== "" && (password.length < 6 || password !== confirmPassword)) {
                        isValid = false;
                        $("#password, #confirm-password").addClass("error");
                        if (password.length < 6) {
                            $("#password").next(".error-message").text("Password must be at least 6 characters.").show();
                        } else {
                            $("#password").next(".error-message").hide();
                        }
                        if (password !== confirmPassword) {
                            $("#confirm-password").next(".error-message").text("Passwords must match.").show();
                        }
                    } else {
                        $("#password, #confirm-password").removeClass("error");
                        $("#password").next(".error-message").hide();
                        $("#confirm-password").next(".error-message").hide();
                    }

                    //validate profile picture URL (optional)
                    const profilePic = $("#profile-picture").val().trim();
                    if (profilePic !== "" && !/\.(jpeg|jpg|png)$/i.test(profilePic)) {
                        isValid = false;
                        $("#profile-picture").addClass("error").next(".error-message").show();
                    } else {
                        $("#profile-picture").removeClass("error").next(".error-message").hide();
                    }

                    //submit if valid
                    if (isValid) {
                        this.submit(); //allow form submission
                    }
                });

                //clean up on input
                $("#user-settings-form input, #user-settings-form textarea").on("input", function () {
                    $(this).removeClass("error").next(".error-message").hide();
                });
            });
        </script>
    </body>
</html>