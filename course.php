<?php
require('bot.php');
require('config.php');
require('function.php');

$token = "TELEGRAM_BOT_API_TOKEN";
$bot = new telegram_bot($token);

$data = $bot->read_post_message();
$message = $data->message;
$date = $message->date;
$chatid = $message->chat->id;
$username = $message->from->username;
$mid = $message->message_id;
$text = @$message->text;
$callback = $data->callback_query->data;
$callback_chatid = $data->callback_query->message->chat->id;
$callback_messageid = $data->callback_query->message->message_id;

$ex = explode(" ", $text);


switch ($text) {

    case '/start':

        $check = $bot->get_chat_member("@free_online_course", $chatid);

        $ischat = $check->result->status;

        if ($ischat == "member" || $ischat == "creator") {
            $query = "SELECT * FROM bot WHERE chat_id = $chatid";
            $result = mysqli_query($db, $query);

            if (mysqli_num_rows($result) > 0) {
                $keyboard = new ReplyKeyboardMarkup(true, true);
                $options = keyboard_options();
                $keyboard->add_option($options);
                $bot->send_message($chatid, "Welcome @$username welcome to @udemyfreerobot!\nI will provide you free online courses, E-Books, Udemy Coupons and many more. Plese use the bot menu for further navigation.", null, json_encode($keyboard), "HTML");
            } else {
                $result1 = mysqli_query($db, "INSERT INTO bot (chat_id, user_name) VALUES ('$chatid', '$username')");
                if ($result1) {
                    $keyboard = new ReplyKeyboardMarkup(true, true);
                    $options = keyboard_options();
                    $keyboard->add_option($options);
                    $bot->send_message($chatid, "HI @$username welcome to @udemyfreerobot!\nI will provide you free online courses, E-Books, Udemy Coupons and many more. Plese use the bot menu for further navigation.", null, json_encode($keyboard), "HTML");
                } else {
                    //$error = mysqli_error($db);
                    $bot->send_message($chatid, "You don't have a username for your telegram account. To create a username GoTo Settings and set your username.", null, null, 'HTML');
                }
            }
        } else {
            $keyboard = new ReplyKeyboardRemove(true, false);
            $bot->send_message($chatid, "You need to be a member of @free_online_course channel. Please join in channel and /start the bot again.", null, json_encode($keyboard), 'HTML');
        }

        break;

    case '📔 E-Books':
        $keyboard = new InlineKeyboardMarkup(true, true);
        $options = ebook_options();
        $keyboard->add_option($options);
        $bot->send_message($chatid, "A - Z Programming E-books for free", null, json_encode($keyboard));
        break;

    case '📚 FREE Courses':
        $keyboard = new InlineKeyboardMarkup(true, true);
        $options = free_course_category_options();
        $keyboard->add_option($options);
        $bot->send_message($chatid, "Select course category", null, json_encode($keyboard));
        break;

    case '🔍 Search Course':
        $bot->send_message($chatid, "🔍 <b>Search Course</b>\n\nAn Advanced method to search FREE udemy courses.\n\n<b>How to Search Course?</b>\n\n<pre>/search YOUR SEARCH TERM</pre>\n\n(e.g) - /search Amazon aws", "HTML", null);
        break;

    case '❓ Help':
        $keyboard = new InlineKeyboardMarkup(true, true);
        $options[0][0] = ["text" => "Share with friends", "url" => "https://telegram.me/share/url?url=https://t.me/udemyfreerobot"];
        $keyboard->add_option($options);
        $bot->send_message($chatid, "❓ Help\n\n<b>How to enroll Course?</b>\nClick on <b>[ENROLL]</b> inline link to enroll course.\n\n<b>Why coupon course asking to pay?</b>\nThis is because of the coupon was expired. ENROLL the coupon course within 3 day of posting. And enroll it for free. Join @free_online_course to get coupon notification.\n\n<b>I don't find the course which i need.</b>\nIf you dont find the course please request it here @Request_courseBot.\n\n🤖<pre>----This BOT was developed by----</pre>🤖\n@Gopi_killer", "HTML", json_encode($keyboard));
        break;

    case '💸 EARN from us':

        $bot->send_message($chatid, "Coming soon...", null, null);
        break;

    case 'Others':

        $bot->send_message($chatid, "For promotion or other services. Please contact @Gopi_killer", null, null);
        break;

    case '♥️ Donate ♥️':
        $keyboard = new InlineKeyboardMarkup(true, true);
        $options[0][0] = ["text" => "♥️ Donate here ♥️", "url" => "https://ko-fi.com/free_online_course"];
        $keyboard->add_option($options);
        $bot->send_message($chatid, "Hello, @$username\n\nIf you're feeling generous, you can contribute to the @udemyfreerobot bot and https://saveitsafer.com website by making a monetary donation of any amount to our bot and website. This will go towards server costs, domain and any time and resources used to develop the site. This is an optional act, however it is greatly appreciated and your name will also be listed publically on website page.", null, json_encode($keyboard));
        break;

    case '💯 COUPON Courses':


        //mysqli_query($db, "UPDATE bot SET coupon_link='$coupon_link', coupon_previous='$coupon_previous', current_page='$current_page', next_page='$next_page', previous_page='$previous_page' WHERE chat_id=$chatid");
        $current_page = 1;
        $url = "https://saveitsafer.com:443/wp-json/wp/v2/posts?_fields=id,name,title,link,count&per_page=10&categories_exclude=15,2,3,4,5,1,26&page=$current_page";
        list($body, $header) = initCurlRequest('GET', $url);
        $total_page = $header['X-WP-TotalPages'];
        $total_courses = $header['X-WP-Total'];

        foreach ($body as $course_results) {
            $mess .= "📚 " . $course_results['title']['rendered'] . " - <a href='" . $course_results['link'] . "'>[ENROLL] </a>" . "\n\n";
        }
        $pagination = "<b>Total Courses : $total_courses | Page $current_page of $total_page</b>";
        $keyboard = new InlineKeyboardMarkup(true, true);
        if ($current_page == 1) {
            $coupon_link = "https://saveitsafer.com:443/wp-json/wp/v2/posts?_fields=id,name,title,link,count&categories_exclude=15,2,3,4,5,1,26&per_page=10&page=";
            $update = mysqli_query($db, "UPDATE bot SET coupon_link='$coupon_link', current_page='$current_page' WHERE chat_id=$chatid");
            $options = coupon_next_options();
        }
        $keyboard->add_option($options);

        $bot->send_message($chatid, $mess . $pagination, 'HTML', json_encode($keyboard));

        break;

    case $ex[0] == '/search':
        $q = str_replace('/search', '', $text);
        if (strlen($q) == 0) {
            $bot->send_message($chatid, "Search term could not be empty", null, null);
        } else {
            $q = rawurlencode($q);
            $url = "https://www.udemy.com/api-2.0/courses/?price=price-free&search=$q&fields[course]=title,headline,image_480x270,url,price";
            $mes = getcourse($url);
            $course_next = $mes->next;
            $course_previous = $mes->previous;
            $course_result = $mes->results;

            if ($mes->count == 0) {
                $bot->send_message($chatid, "Course not found!", 'HTML', null);
                die();
            }

            foreach ($course_result as $course_results) {
                $mess .= "📚 " . $course_results->title . " - <a href='https://course.saveitsafer.com/index.php?id=" . $course_results->id . "'>[ENROLL] </a>" . "\n\n";
            }

            $keyboard = new InlineKeyboardMarkup(true, true);
            if ($course_previous == null) {
                $update = mysqli_query($db, "UPDATE bot SET next='$course_next' WHERE chat_id=$chatid");
                $options = next_options();
            } elseif ($course_next == null) {
                $update = mysqli_query($db, "UPDATE bot SET previous='$course_previous' WHERE chat_id=$chatid");
                $options = previous_options();
            } else {
                $update = mysqli_query($db, "UPDATE bot SET previous='$course_previous', next='$course_next' WHERE chat_id=$chatid");
                $options = next_previous();
            }
            $keyboard->add_option($options);

            $bot->send_message($chatid, $mess, 'HTML', json_encode($keyboard));
        }
        break;

    default:
        $bot->send_message($chatid, "Invalid command", null, null);
        break;
}

switch ($callback) {
    case '1':
        $bot->send_document($callback_chatid, "https://goalkicker.com/DotNETFrameworkBook/DotNETFrameworkNotesForProfessionals.pdf", "<b>.NET Framework</b> - @udemyfreerobot", "HTML");
        break;

    case '2':
        $bot->send_document($callback_chatid, "https://goalkicker.com/AlgorithmsBook/AlgorithmsNotesForProfessionals.pdf", "<b>Algorithms</b> - @udemyfreerobot", "HTML");
        break;

    case '3':
        $bot->send_document($callback_chatid, "https://goalkicker.com/AndroidBook/AndroidNotesForProfessionals.pdf", "<b>Android</b> - @udemyfreerobot", "HTML");
        break;

    case '4':
        $bot->send_document($callback_chatid, "https://goalkicker.com/Angular2Book/Angular2NotesForProfessionals.pdf", "<b>Angular 2</b> - @udemyfreerobot", "HTML");
        break;

    case '5':
        $bot->send_document($callback_chatid, "https://goalkicker.com/AngularJSBook/AngularJSNotesForProfessionals.pdf", "<b>Angular JS</b> - @udemyfreerobot", "HTML");
        break;

    case '6':
        $bot->send_document($callback_chatid, "https://goalkicker.com/BashBook/BashNotesForProfessionals.pdf", "<b>Bash</b> - @udemyfreerobot", "HTML");
        break;

    case '7':
        $bot->send_document($callback_chatid, "https://goalkicker.com/CBook/CNotesForProfessionals.pdf", "<b>C</b> - @udemyfreerobot", "HTML");
        break;

    case '8':
        $bot->send_document($callback_chatid, "https://goalkicker.com/CPlusPlusBook/CPlusPlusNotesForProfessionals.pdf", "<b>C++</b> - @udemyfreerobot", "HTML");
        break;

    case '9':
        $bot->send_document($callback_chatid, "https://goalkicker.com/CSharpBook/CSharpNotesForProfessionals.pdf", "<b>C Sharp</b> - @udemyfreerobot", "HTML");
        break;

    case '10':
        $bot->send_document($callback_chatid, "https://goalkicker.com/CSSBook/CSSNotesForProfessionals.pdf", "<b>CSS</b> - @udemyfreerobot", "HTML");
        break;

    case '11':
        $bot->send_document($callback_chatid, "https://goalkicker.com/EntityFrameworkBook/EntityFrameworkNotesForProfessionals.pdf", "<b>Entity Framework</b> - @udemyfreerobot", "HTML");
        break;

    case '12':
        $bot->send_document($callback_chatid, "https://goalkicker.com/ExcelVBABook/ExcelVBANotesForProfessionals.pdf", "<b>Excel VBA</b> - @udemyfreerobot", "HTML");
        break;

    case '13':
        $bot->send_document($callback_chatid, "https://goalkicker.com/GitBook/GitNotesForProfessionals.pdf", "<b>Git</b> - @udemyfreerobot", "HTML");
        break;

    case '14':
        $bot->send_document($callback_chatid, "https://goalkicker.com/HaskellBook/HaskellNotesForProfessionals.pdf", "<b>Haskell</b> - @udemyfreerobot", "HTML");
        break;

    case '15':
        $bot->send_document($callback_chatid, "https://goalkicker.com/HibernateBook/HibernateNotesForProfessionals.pdf", "<b>Hibernate</b> - @udemyfreerobot", "HTML");
        break;

    case '16':
        $bot->send_document($callback_chatid, "https://goalkicker.com/HTML5Book/HTML5NotesForProfessionals.pdf", "<b>HTML5</b> - @udemyfreerobot", "HTML");
        break;

    case '17':
        $bot->send_document($callback_chatid, "https://goalkicker.com/HTML5CanvasBook/HTML5CanvasNotesForProfessionals.pdf", "<b>HTML5 Canvas</b> - @udemyfreerobot", "HTML");
        break;

    case '18':
        $bot->send_document($callback_chatid, "https://goalkicker.com/iOSBook/iOSNotesForProfessionals.pdf", "<b>iOS</b> - @udemyfreerobot", "HTML");
        break;

    case '19':
        $bot->send_document($callback_chatid, "https://goalkicker.com/JavaBook/JavaNotesForProfessionals.pdf", "<b>Java</b> - @udemyfreerobot", "HTML");
        break;

    case '20':
        $bot->send_document($callback_chatid, "https://goalkicker.com/JavaScriptBook/JavaScriptNotesForProfessionals.pdf", "<b>JavaScript</b> - @udemyfreerobot", "HTML");
        break;

    case '21':
        $bot->send_document($callback_chatid, "https://goalkicker.com/jQueryBook/jQueryNotesForProfessionals.pdf", "<b>jQuery</b> - @udemyfreerobot", "HTML");
        break;

    case '22':
        $bot->send_document($callback_chatid, "https://goalkicker.com/KotlinBook/KotlinNotesForProfessionals.pdf", "<b>KotlinN</b> - @udemyfreerobot", "HTML");
        break;

    case '23':
        $bot->send_document($callback_chatid, "https://goalkicker.com/LaTeXBook/LaTeXNotesForProfessionals.pdf", "<b>LaTeX</b> - @udemyfreerobot", "HTML");
        break;

    case '24':
        $bot->send_document($callback_chatid, "https://goalkicker.com/LinuxBook/LinuxNotesForProfessionals.pdf", "<b>Linux</b> - @udemyfreerobot", "HTML");
        break;

    case '25':
        $bot->send_document($callback_chatid, "https://goalkicker.com/MATLABBook/MATLABNotesForProfessionals.pdf", "<b>MAT LAB</b> - @udemyfreerobot", "HTML");
        break;

    case '26':
        $bot->send_document($callback_chatid, "https://goalkicker.com/MicrosoftSQLServerBook/MicrosoftSQLServerNotesForProfessionals.pdf", "<b>Microsoft SQL Server</b> - @udemyfreerobot", "HTML");
        break;

    case '27':
        $bot->send_document($callback_chatid, "https://goalkicker.com/MongoDBBook/MongoDBNotesForProfessionals.pdf", "<b>Mongo DB</b> - @udemyfreerobot", "HTML");
        break;

    case '28':
        $bot->send_document($callback_chatid, "https://goalkicker.com/MySQLBook/MySQLNotesForProfessionals.pdf", "<b>MySQL</b> - @udemyfreerobot", "HTML");
        break;

    case '29':
        $bot->send_document($callback_chatid, "https://goalkicker.com/NodeJSBook/NodeJSNotesForProfessionals.pdf", "<b>Node JS</b> - @udemyfreerobot", "HTML");
        break;

    case '30':
        $bot->send_document($callback_chatid, "https://goalkicker.com/ObjectiveCBook/ObjectiveCNotesForProfessionals.pdf", "<b>Objective C</b> - @udemyfreerobot", "HTML");
        break;

    case '31':
        $bot->send_document($callback_chatid, "https://goalkicker.com/OracleDatabaseBook/OracleDatabaseNotesForProfessionals.pdf", "<b>OracleDatabase</b> - @udemyfreerobot", "HTML");
        break;

    case '32':
        $bot->send_document($callback_chatid, "https://goalkicker.com/PerlBook/PerlNotesForProfessionals.pdf", "<b>Perl</b> - @udemyfreerobot", "HTML");
        break;

    case '33':
        $bot->send_document($callback_chatid, "https://goalkicker.com/PHPBook/PHPNotesForProfessionals.pdf", "<b>PHP</b> - @udemyfreerobot", "HTML");
        break;

    case '34':
        $bot->send_document($callback_chatid, "https://goalkicker.com/PostgreSQLBook/PostgreSQLNotesForProfessionals.pdf", "<b>Postgre SQL</b> - @udemyfreerobot", "HTML");
        break;

    case '35':
        $bot->send_document($callback_chatid, "https://goalkicker.com/PowerShellBook/PowerShellNotesForProfessionals.pdf", "<b>Power Shell</b> - @udemyfreerobot", "HTML");
        break;

    case '36':
        $bot->send_document($callback_chatid, "https://goalkicker.com/PythonBook/PythonNotesForProfessionals.pdf", "<b>Python</b> - @udemyfreerobot", "HTML");
        break;

    case '37':
        $bot->send_document($callback_chatid, "https://goalkicker.com/RBook/RNotesForProfessionals.pdf", "<b>R Programming</b> - @udemyfreerobot", "HTML");
        break;

    case '38':
        $bot->send_document($callback_chatid, "https://goalkicker.com/ReactJSBook/ReactJSNotesForProfessionals.pdf", "<b>React JS</b> - @udemyfreerobot", "HTML");
        break;

    case '39':
        $bot->send_document($callback_chatid, "https://goalkicker.com/ReactNativeBook/ReactNativeNotesForProfessionals.pdf", "<b>React Native</b> - @udemyfreerobot", "HTML");
        break;

    case '40':
        $bot->send_document($callback_chatid, "https://goalkicker.com/RubyBook/RubyNotesForProfessionals.pdf", "<b>Ruby</b> - @udemyfreerobot", "HTML");
        break;

    case '41':
        $bot->send_document($callback_chatid, "https://goalkicker.com/RubyOnRailsBook/RubyOnRailsNotesForProfessionals.pdf", "<b>Ruby Rails</b> - @udemyfreerobot", "HTML");
        break;

    case '42':
        $bot->send_document($callback_chatid, "https://goalkicker.com/SpringFrameworkBook/SpringFrameworkNotesForProfessionals.pdf", "<b>Spring FrameworkBook/Spring Framework</b> - @udemyfreerobot", "HTML");
        break;

    case '43':
        $bot->send_document($callback_chatid, "https://goalkicker.com/SQLBook/SQLNotesForProfessionals.pdf", "<b>SQL Notes</b> - @udemyfreerobot", "HTML");
        break;

    case '44':
        $bot->send_document($callback_chatid, "https://goalkicker.com/SwiftBook/SwiftNotesForProfessionals.pdf", "<b>Swift Notes</b> - @udemyfreerobot", "HTML");
        break;

    case '45':
        $bot->send_document($callback_chatid, "https://goalkicker.com/TypeScriptBook2/TypeScriptNotesForProfessionals.pdf", "<b>Type Script</b> - @udemyfreerobot", "HTML");
        break;

    case '46':
        $bot->send_document($callback_chatid, "https://goalkicker.com/VBABook/VBANotesForProfessionals.pdf", "<b>VBA</b> - @udemyfreerobot", "HTML");
        break;

    case '47':
        $bot->send_document($callback_chatid, "https://goalkicker.com/VisualBasic_NETBook/VisualBasic_NETNotesForProfessionals.pdf", "<b>Visual Basic .NET</b> by - @udemyfreerobot", "HTML");
        break;

    case '48':
        $bot->send_document($callback_chatid, "https://goalkicker.com/XamarinFormsBook/XamarinFormsNotesForProfessionals.pdf", "<b>Xamarin Forms</b> by - @udemyfreerobot", "HTML");
        break;

    case 'Business':
        $url = "https://www.udemy.com/api-2.0/courses/?price=price-free&category=Business&fields[course]=title,headline,image_480x270,url,price";

        $mes = getcourse($url);
        $course_next = $mes->next;
        $course_previous = $mes->previous;
        $course_result = $mes->results;

        foreach ($course_result as $course_results) {
            $mess .= "📚 " . $course_results->title . " - <a href='https://course.saveitsafer.com/index.php?id=" . $course_results->id . "'>[ENROLL] </a>" . "\n\n";
        }

        $keyboard = new InlineKeyboardMarkup(true, true);
        if ($course_previous == null) {
            $update = mysqli_query($db, "UPDATE bot SET next='$course_next' WHERE chat_id=$callback_chatid");
            $options = next_options();
        } elseif ($course_next == null) {
            $update = mysqli_query($db, "UPDATE bot SET previous='$course_previous' WHERE chat_id=$callback_chatid");
            $options = previous_options();
        } else {
            $update = mysqli_query($db, "UPDATE bot SET previous='$course_previous', next='$course_next' WHERE chat_id=$callback_chatid");
            $options = next_previous();
        }
        $keyboard->add_option($options);

        $bot->edit_message($callback_chatid, $callback_messageid, $mess, null, 'HTML', true, json_encode($keyboard));

        break;

    case 'Design':
        $url = "https://www.udemy.com/api-2.0/courses/?price=price-free&category=Design&fields[course]=title,headline,image_480x270,url,price";

        $mes = getcourse($url);
        $course_next = $mes->next;
        $course_previous = $mes->previous;
        $course_result = $mes->results;

        foreach ($course_result as $course_results) {
            $mess .= "📚 " . $course_results->title . " - <a href='https://course.saveitsafer.com/index.php?id=" . $course_results->id . "'>[ENROLL] </a>" . "\n\n";
        }

        $keyboard = new InlineKeyboardMarkup(true, true);
        if ($course_previous == null) {
            $update = mysqli_query($db, "UPDATE bot SET next='$course_next' WHERE chat_id=$callback_chatid");
            $options = next_options();
        } elseif ($course_next == null) {
            $update = mysqli_query($db, "UPDATE bot SET previous='$course_previous' WHERE chat_id=$callback_chatid");
            $options = previous_options();
        } else {
            $update = mysqli_query($db, "UPDATE bot SET previous='$course_previous', next='$course_next' WHERE chat_id=$callback_chatid");
            $options = next_previous();
        }
        $keyboard->add_option($options);

        $bot->edit_message($callback_chatid, $callback_messageid, $mess, null, 'HTML', true, json_encode($keyboard));

        break;

    case 'Development':
        $url = "https://www.udemy.com/api-2.0/courses/?price=price-free&category=Development&fields[course]=title,headline,image_480x270,url,price";

        $mes = getcourse($url);
        $course_next = $mes->next;
        $course_previous = $mes->previous;
        $course_result = $mes->results;

        foreach ($course_result as $course_results) {
            $mess .= "📚 " . $course_results->title . " - <a href='https://course.saveitsafer.com/index.php?id=" . $course_results->id . "'>[ENROLL] </a>" . "\n\n";
        }

        $keyboard = new InlineKeyboardMarkup(true, true);
        if ($course_previous == null) {
            $update = mysqli_query($db, "UPDATE bot SET next='$course_next' WHERE chat_id=$callback_chatid");
            $options = next_options();
        } elseif ($course_next == null) {
            $update = mysqli_query($db, "UPDATE bot SET previous='$course_previous' WHERE chat_id=$callback_chatid");
            $options = previous_options();
        } else {
            $update = mysqli_query($db, "UPDATE bot SET previous='$course_previous', next='$course_next' WHERE chat_id=$callback_chatid");
            $options = next_previous();
        }
        $keyboard->add_option($options);

        $bot->edit_message($callback_chatid, $callback_messageid, $mess, null, 'HTML', true, json_encode($keyboard));

        break;

    case 'Finance & Accounting':
        $url = "https://www.udemy.com/api-2.0/courses/?price=price-free&category=Finance%20%26%20Accounting&fields[course]=title,headline,image_480x270,url,price";

        $mes = getcourse($url);
        $course_next = $mes->next;
        $course_previous = $mes->previous;
        $course_result = $mes->results;

        foreach ($course_result as $course_results) {
            $mess .= "📚 " . $course_results->title . " - <a href='https://course.saveitsafer.com/index.php?id=" . $course_results->id . "'>[ENROLL] </a>" . "\n\n";
        }

        $keyboard = new InlineKeyboardMarkup(true, true);
        if ($course_previous == null) {
            $update = mysqli_query($db, "UPDATE bot SET next='$course_next' WHERE chat_id=$callback_chatid");
            $options = next_options();
        } elseif ($course_next == null) {
            $update = mysqli_query($db, "UPDATE bot SET previous='$course_previous' WHERE chat_id=$callback_chatid");
            $options = previous_options();
        } else {
            $update = mysqli_query($db, "UPDATE bot SET previous='$course_previous', next='$course_next' WHERE chat_id=$callback_chatid");
            $options = next_previous();
        }
        $keyboard->add_option($options);

        $bot->edit_message($callback_chatid, $callback_messageid, $mess, null, 'HTML', true, json_encode($keyboard));

        break;

    case 'Health & Fitness':
        $url = "https://www.udemy.com/api-2.0/courses/?price=price-free&category=Health%20%26%20Fitness&fields[course]=title,headline,image_480x270,url,price";

        $mes = getcourse($url);
        $course_next = $mes->next;
        $course_previous = $mes->previous;
        $course_result = $mes->results;

        foreach ($course_result as $course_results) {
            $mess .= "📚 " . $course_results->title . " - <a href='https://course.saveitsafer.com/index.php?id=" . $course_results->id . "'>[ENROLL] </a>" . "\n\n";
        }

        $keyboard = new InlineKeyboardMarkup(true, true);
        if ($course_previous == null) {
            $update = mysqli_query($db, "UPDATE bot SET next='$course_next' WHERE chat_id=$callback_chatid");
            $options = next_options();
        } elseif ($course_next == null) {
            $update = mysqli_query($db, "UPDATE bot SET previous='$course_previous' WHERE chat_id=$callback_chatid");
            $options = previous_options();
        } else {
            $update = mysqli_query($db, "UPDATE bot SET previous='$course_previous', next='$course_next' WHERE chat_id=$callback_chatid");
            $options = next_previous();
        }
        $keyboard->add_option($options);

        $bot->edit_message($callback_chatid, $callback_messageid, $mess, null, 'HTML', true, json_encode($keyboard));

        break;

    case 'IT & Software':
        $url = "https://www.udemy.com/api-2.0/courses/?price=price-free&category=IT%20%26%20Software&fields[course]=title,headline,image_480x270,url,price";

        $mes = getcourse($url);
        $course_next = $mes->next;
        $course_previous = $mes->previous;
        $course_result = $mes->results;

        foreach ($course_result as $course_results) {
            $mess .= "📚 " . $course_results->title . " - <a href='https://course.saveitsafer.com/index.php?id=" . $course_results->id . "'>[ENROLL] </a>" . "\n\n";
        }

        $keyboard = new InlineKeyboardMarkup(true, true);
        if ($course_previous == null) {
            $update = mysqli_query($db, "UPDATE bot SET next='$course_next' WHERE chat_id=$callback_chatid");
            $options = next_options();
        } elseif ($course_next == null) {
            $update = mysqli_query($db, "UPDATE bot SET previous='$course_previous' WHERE chat_id=$callback_chatid");
            $options = previous_options();
        } else {
            $update = mysqli_query($db, "UPDATE bot SET previous='$course_previous', next='$course_next' WHERE chat_id=$callback_chatid");
            $options = next_previous();
        }
        $keyboard->add_option($options);

        $bot->edit_message($callback_chatid, $callback_messageid, $mess, null, 'HTML', true, json_encode($keyboard));

        break;

    case 'Lifestyle':
        $url = "https://www.udemy.com/api-2.0/courses/?price=price-free&category=Lifestyle&fields[course]=title,headline,image_480x270,url,price";

        $mes = getcourse($url);
        $course_next = $mes->next;
        $course_previous = $mes->previous;
        $course_result = $mes->results;

        foreach ($course_result as $course_results) {
            $mess .= "📚 " . $course_results->title . " - <a href='https://course.saveitsafer.com/index.php?id=" . $course_results->id . "'>[ENROLL] </a>" . "\n\n";
        }

        $keyboard = new InlineKeyboardMarkup(true, true);
        if ($course_previous == null) {
            $update = mysqli_query($db, "UPDATE bot SET next='$course_next' WHERE chat_id=$callback_chatid");
            $options = next_options();
        } elseif ($course_next == null) {
            $update = mysqli_query($db, "UPDATE bot SET previous='$course_previous' WHERE chat_id=$callback_chatid");
            $options = previous_options();
        } else {
            $update = mysqli_query($db, "UPDATE bot SET previous='$course_previous', next='$course_next' WHERE chat_id=$callback_chatid");
            $options = next_previous();
        }
        $keyboard->add_option($options);

        $bot->edit_message($callback_chatid, $callback_messageid, $mess, null, 'HTML', true, json_encode($keyboard));

        break;

    case 'Marketing':
        $url = "https://www.udemy.com/api-2.0/courses/?price=price-free&category=Marketing&fields[course]=title,headline,image_480x270,url,price";

        $mes = getcourse($url);
        $course_next = $mes->next;
        $course_previous = $mes->previous;
        $course_result = $mes->results;

        foreach ($course_result as $course_results) {
            $mess .= "📚 " . $course_results->title . " - <a href='https://course.saveitsafer.com/index.php?id=" . $course_results->id . "'>[ENROLL] </a>" . "\n\n";
        }

        $keyboard = new InlineKeyboardMarkup(true, true);
        if ($course_previous == null) {
            $update = mysqli_query($db, "UPDATE bot SET next='$course_next' WHERE chat_id=$callback_chatid");
            $options = next_options();
        } elseif ($course_next == null) {
            $update = mysqli_query($db, "UPDATE bot SET previous='$course_previous' WHERE chat_id=$callback_chatid");
            $options = previous_options();
        } else {
            $update = mysqli_query($db, "UPDATE bot SET previous='$course_previous', next='$course_next' WHERE chat_id=$callback_chatid");
            $options = next_previous();
        }
        $keyboard->add_option($options);

        $bot->edit_message($callback_chatid, $callback_messageid, $mess, null, 'HTML', true, json_encode($keyboard));

        break;

    case 'Music':
        $url = "https://www.udemy.com/api-2.0/courses/?price=price-free&category=Music&fields[course]=title,headline,image_480x270,url,price";

        $mes = getcourse($url);
        $course_next = $mes->next;
        $course_previous = $mes->previous;
        $course_result = $mes->results;

        foreach ($course_result as $course_results) {
            $mess .= "📚 " . $course_results->title . " - <a href='https://course.saveitsafer.com/index.php?id=" . $course_results->id . "'>[ENROLL] </a>" . "\n\n";
        }

        $keyboard = new InlineKeyboardMarkup(true, true);
        if ($course_previous == null) {
            $update = mysqli_query($db, "UPDATE bot SET next='$course_next' WHERE chat_id=$callback_chatid");
            $options = next_options();
        } elseif ($course_next == null) {
            $update = mysqli_query($db, "UPDATE bot SET previous='$course_previous' WHERE chat_id=$callback_chatid");
            $options = previous_options();
        } else {
            $update = mysqli_query($db, "UPDATE bot SET previous='$course_previous', next='$course_next' WHERE chat_id=$callback_chatid");
            $options = next_previous();
        }
        $keyboard->add_option($options);

        $bot->edit_message($callback_chatid, $callback_messageid, $mess, null, 'HTML', true, json_encode($keyboard));

        break;

    case 'Office Productivity':
        $url = "https://www.udemy.com/api-2.0/courses/?price=price-free&category=Office%20Productivity&fields[course]=title,headline,image_480x270,url,price";

        $mes = getcourse($url);
        $course_next = $mes->next;
        $course_previous = $mes->previous;
        $course_result = $mes->results;

        foreach ($course_result as $course_results) {
            $mess .= "📚 " . $course_results->title . " - <a href='https://course.saveitsafer.com/index.php?id=" . $course_results->id . "'>[ENROLL] </a>" . "\n\n";
        }

        $keyboard = new InlineKeyboardMarkup(true, true);
        if ($course_previous == null) {
            $update = mysqli_query($db, "UPDATE bot SET next='$course_next' WHERE chat_id=$callback_chatid");
            $options = next_options();
        } elseif ($course_next == null) {
            $update = mysqli_query($db, "UPDATE bot SET previous='$course_previous' WHERE chat_id=$callback_chatid");
            $options = previous_options();
        } else {
            $update = mysqli_query($db, "UPDATE bot SET previous='$course_previous', next='$course_next' WHERE chat_id=$callback_chatid");
            $options = next_previous();
        }
        $keyboard->add_option($options);

        $bot->edit_message($callback_chatid, $callback_messageid, $mess, null, 'HTML', true, json_encode($keyboard));

        break;

    case 'Personal Development':
        $url = "https://www.udemy.com/api-2.0/courses/?price=price-free&category=Personal%20Development&fields[course]=title,headline,image_480x270,url,price";

        $mes = getcourse($url);
        $course_next = $mes->next;
        $course_previous = $mes->previous;
        $course_result = $mes->results;

        foreach ($course_result as $course_results) {
            $mess .= "📚 " . $course_results->title . " - <a href='https://course.saveitsafer.com/index.php?id=" . $course_results->id . "'>[ENROLL] </a>" . "\n\n";
        }

        $keyboard = new InlineKeyboardMarkup(true, true);
        if ($course_previous == null) {
            $update = mysqli_query($db, "UPDATE bot SET next='$course_next' WHERE chat_id=$callback_chatid");
            $options = next_options();
        } elseif ($course_next == null) {
            $update = mysqli_query($db, "UPDATE bot SET previous='$course_previous' WHERE chat_id=$callback_chatid");
            $options = previous_options();
        } else {
            $update = mysqli_query($db, "UPDATE bot SET previous='$course_previous', next='$course_next' WHERE chat_id=$callback_chatid");
            $options = next_previous();
        }
        $keyboard->add_option($options);

        $bot->edit_message($callback_chatid, $callback_messageid, $mess, null, 'HTML', true, json_encode($keyboard));

        break;

    case 'Photography & Video':
        $url = "https://www.udemy.com/api-2.0/courses/?price=price-free&category=Photography%20%26%20Video&fields[course]=title,headline,image_480x270,url,price";

        $mes = getcourse($url);
        $course_next = $mes->next;
        $course_previous = $mes->previous;
        $course_result = $mes->results;

        foreach ($course_result as $course_results) {
            $mess .= "📚 " . $course_results->title . " - <a href='https://course.saveitsafer.com/index.php?id=" . $course_results->id . "'>[ENROLL]</a>" . "\n\n";
        }

        $keyboard = new InlineKeyboardMarkup(true, true);
        if ($course_previous == null) {
            $update = mysqli_query($db, "UPDATE bot SET next='$course_next' WHERE chat_id=$callback_chatid");
            $options = next_options();
        } elseif ($course_next == null) {
            $update = mysqli_query($db, "UPDATE bot SET previous='$course_previous' WHERE chat_id=$callback_chatid");
            $options = previous_options();
        } else {
            $update = mysqli_query($db, "UPDATE bot SET previous='$course_previous', next='$course_next' WHERE chat_id=$callback_chatid");
            $options = next_previous();
        }
        $keyboard->add_option($options);

        $bot->edit_message($callback_chatid, $callback_messageid, $mess, null, 'HTML', true, json_encode($keyboard));

        break;

    case 'Next':

        $res = mysqli_query($db, "SELECT next FROM bot WHERE chat_id=$callback_chatid");
        $row = mysqli_fetch_assoc($res);
        $url = $row['next'];

        $mes = getcourse($url);
        $course_next = $mes->next;
        $course_previous = $mes->previous;
        $course_result = $mes->results;

        foreach ($course_result as $course_results) {
            $mess .= "📚 " . $course_results->title . " - <a href='https://course.saveitsafer.com/index.php?id=" . $course_results->id . "'>[ENROLL] </a>" . "\n\n";
        }

        $keyboard = new InlineKeyboardMarkup(true, true);
        if ($course_previous == null) {
            $update = mysqli_query($db, "UPDATE bot SET next='$course_next' WHERE chat_id=$callback_chatid");
            $options = next_options();
        } elseif ($course_next == null) {
            $update = mysqli_query($db, "UPDATE bot SET previous='$course_previous' WHERE chat_id=$callback_chatid");
            $options = previous_options();
        } else {
            $update = mysqli_query($db, "UPDATE bot SET previous='$course_previous', next='$course_next' WHERE chat_id=$callback_chatid");
            $options = next_previous();
        }
        $keyboard->add_option($options);

        $bot->edit_message($callback_chatid, $callback_messageid, $mess, null, 'HTML', true, json_encode($keyboard));

        break;

    case 'Previous':

        $res = mysqli_query($db, "SELECT previous FROM bot WHERE chat_id=$callback_chatid");
        $row = mysqli_fetch_assoc($res);
        $url = $row['previous'];

        $mes = getcourse($url);
        $course_next = $mes->next;
        $course_previous = $mes->previous;
        $course_result = $mes->results;

        foreach ($course_result as $course_results) {
            $mess .= "📚 " . $course_results->title . " - <a href='https://course.saveitsafer.com/index.php?id=" . $course_results->id . "'>[ENROLL] </a>" . "\n\n";
        }

        $keyboard = new InlineKeyboardMarkup(true, true);
        if ($course_previous == null) {
            $update = mysqli_query($db, "UPDATE bot SET next='$course_next' WHERE chat_id=$callback_chatid");
            $options = next_options();
        } elseif ($course_next == null) {
            $update = mysqli_query($db, "UPDATE bot SET previous='$course_previous' WHERE chat_id=$callback_chatid");
            $options = previous_options();
        } else {
            $update = mysqli_query($db, "UPDATE bot SET previous='$course_previous', next='$course_next' WHERE chat_id=$callback_chatid");
            $options = next_previous();
        }
        $keyboard->add_option($options);

        $bot->edit_message($callback_chatid, $callback_messageid, $mess, null, 'HTML', true, json_encode($keyboard));

        break;

    case 'Back':
        $keyboard = new InlineKeyboardMarkup(true, true);
        $options = free_course_category_options();
        $keyboard->add_option($options);
        $bot->edit_message($callback_chatid, $callback_messageid, "Select course category", null, null, null, json_encode($keyboard));
        break;

        // COUPON coursee starthere

    case 'NextCoupon':

        $res = mysqli_query($db, "SELECT coupon_link, current_page FROM bot WHERE chat_id=$callback_chatid");
        $row = mysqli_fetch_assoc($res);

        $current_page = $row['current_page'] + 1;
        $url = $row['coupon_link'] . $current_page;

        list($body, $header) = initCurlRequest('GET', $url);
        $total_page = $header['X-WP-TotalPages'];
        $total_courses = $header['X-WP-Total'];

        foreach ($body as $course_results) {
            $mess .= "📚 " . $course_results['title']['rendered'] . " - <a href='" . $course_results['link'] . "'>[ENROLL] </a>" . "\n\n";
        }
        $pagination = "<b>Total Courses : $total_courses | Page $current_page of $total_page</b>";

        $keyboard = new InlineKeyboardMarkup(true, true);
        if ($current_page == $total_page) {
            $update = mysqli_query($db, "UPDATE bot SET current_page='$current_page' WHERE chat_id=$callback_chatid");
            $options = coupon_previous_options();
        } else {
            $update = mysqli_query($db, "UPDATE bot SET current_page='$current_page' WHERE chat_id=$callback_chatid");
            $options = coupon_next_previous();
        }
        $keyboard->add_option($options);

        $bot->edit_message($callback_chatid, $callback_messageid, $mess . $pagination, null, 'HTML', true, json_encode($keyboard));

        break;

    case 'PreviousCoupon':

        $res = mysqli_query($db, "SELECT coupon_link, current_page FROM bot WHERE chat_id=$callback_chatid");
        $row = mysqli_fetch_assoc($res);

        $current_page = $row['current_page'] - 1;
        $url = $row['coupon_link'] . $current_page;

        list($body, $header) = initCurlRequest('GET', $url);
        $total_page = $header['X-WP-TotalPages'];
        $total_courses = $header['X-WP-Total'];

        foreach ($body as $course_results) {
            $mess .= "📚 " . $course_results['title']['rendered'] . " - <a href='" . $course_results['link'] . "'>[ENROLL] </a>" . "\n\n";
        }
        $pagination = "<b>Total Courses : $total_courses | Page $current_page of $total_page</b>";

        $keyboard = new InlineKeyboardMarkup(true, true);
        if ($current_page == 1) {
            $update = mysqli_query($db, "UPDATE bot SET current_page='$current_page' WHERE chat_id=$callback_chatid");
            $options = coupon_next_options();
        } else {
            $update = mysqli_query($db, "UPDATE bot SET current_page='$current_page' WHERE chat_id=$callback_chatid");
            $options = coupon_next_previous();
        }
        $keyboard->add_option($options);

        $bot->edit_message($callback_chatid, $callback_messageid, $mess . $pagination, null, 'HTML', true, json_encode($keyboard));

        break;

    default:
        //$bot->send_message($chatid, "Invalid command", null , null);
        break;
}
