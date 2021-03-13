<?php

function getcourse($url)
{

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization:Basic VFY1d002OWtJY1NSV25NSFA2UnFGMFZGakxiMFlKNEUxd2g1cENsSDpqanpZY0ZBY3BOeWt0ZDhQQ2tuejMyT2Z6bURGc1JObXVXbkZZYXhxQjJWeVFTR1RKb1BTWWFDN045UFBleDNhQVoyMnZKY1hkM1FIbHliQVJZRjhWVk9lUk9QaWxYYk1GTDRHRUZpaGF4N25Jb0c1RUdIdkh2b29LaHU0VGF6bg=='));
    curl_setopt($ch, CURLINFO_HEADER_OUT, true);
    $result = curl_exec($ch);

    curl_close($ch);

    $result = json_decode($result);
    return $result;
}

function initCurlRequest($reqType, $reqURL, $reqBody = '', $headers = array())
{
    if (!in_array($reqType, array('GET', 'POST', 'PUT', 'DELETE'))) {
        throw new Exception('Curl first parameter must be "GET", "POST", "PUT" or "DELETE"');
    }

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $reqURL);
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $reqType);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $reqBody);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_HEADER, true);

    $body = curl_exec($ch);

    // extract header
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($body, 0, $headerSize);
    $header = getHeaders($header);

    // extract body
    $body = json_decode(substr($body, $headerSize), true);

    curl_close($ch);

    return [$body, $header];
}

function getHeaders($respHeaders)
{
    $headers = array();

    $headerText = substr($respHeaders, 0, strpos($respHeaders, "\r\n\r\n"));

    foreach (explode("\r\n", $headerText) as $i => $line) {
        if ($i === 0) {
            $headers['http_code'] = $line;
        } else {
            list($key, $value) = explode(': ', $line);

            $headers[$key] = $value;
        }
    }

    return $headers;
}


function keyboard_options()
{
    $options[0][0] = "📚 FREE Courses";
    $options[0][1] = "💯 COUPON Courses";
    $options[1][0] = "📔 E-Books";
    $options[2][0] = "🔍 Search Course";
    $options[2][1] = "❓ Help";
    $options[3][0] = "💸 EARN from us";
    $options[3][1] = "Others";
    $options[4][0] = "♥️ Donate ♥️";

    return $options;
}

function free_course_category_options()
{
    $options[0][0] = ["text" => "Business", "callback_data" => "Business"];
    $options[0][1] = ["text" => "Design", "callback_data" => "Design"];
    $options[1][0] = ["text" => "Development", "callback_data" => "Development"];
    $options[1][1] = ["text" => "Finance & Accounting", "callback_data" => "Finance & Accounting"];
    $options[2][0] = ["text" => "Health & Fitness", "callback_data" => "Health & Fitness"];
    $options[2][1] = ["text" => "IT & Software", "callback_data" => "IT & Software"];
    $options[3][0] = ["text" => "Lifestyle", "callback_data" => "Lifestyle"];
    $options[3][1] = ["text" => "Marketing", "callback_data" => "Marketing"];
    $options[4][0] = ["text" => "Music", "callback_data" => "Music"];
    $options[4][1] = ["text" => "Office Productivity", "callback_data" => "Office Productivity"];
    $options[5][0] = ["text" => "Personal Development", "callback_data" => "Personal Development"];
    $options[5][1] = ["text" => "Photography & Video", "callback_data" => "Photography & Video"];

    return $options;
}

function ebook_options()
{
    $options[0][0] = ["text" => ".NET Framework", "callback_data" => "1"];
    $options[0][1] = ["text" => "Algorithms", "callback_data" => "2"];
    $options[1][0] = ["text" => "Android®", "callback_data" => "3"];
    $options[1][1] = ["text" => "Angular 2", "callback_data" => "4"];
    $options[2][0] = ["text" => "AngularJS", "callback_data" => "5"];
    $options[2][1] = ["text" => "Bash", "callback_data" => "6"];
    $options[3][0] = ["text" => "C", "callback_data" => "7"];
    $options[3][1] = ["text" => "C++", "callback_data" => "8"];
    $options[4][0] = ["text" => "C#", "callback_data" => "9"];
    $options[4][1] = ["text" => "CSS", "callback_data" => "10"];
    $options[5][0] = ["text" => "Entity Framework", "callback_data" => "11"];
    $options[5][1] = ["text" => "Excel® VBA", "callback_data" => "12"];
    $options[6][0] = ["text" => "Git®", "callback_data" => "13"];
    $options[6][1] = ["text" => "Haskell", "callback_data" => "14"];
    $options[7][0] = ["text" => "Hibernate", "callback_data" => "15"];
    $options[7][1] = ["text" => "HTML5", "callback_data" => "16"];
    $options[8][0] = ["text" => "HTML5 Canvas", "callback_data" => "17"];
    $options[8][1] = ["text" => "iOS®", "callback_data" => "18"];
    $options[9][0] = ["text" => "Java®", "callback_data" => "19"];
    $options[9][1] = ["text" => "JavaScript®", "callback_data" => "20"];
    $options[10][0] = ["text" => "jQuery®", "callback_data" => "21"];
    $options[10][1] = ["text" => "Kotlin®", "callback_data" => "22"];
    $options[11][0] = ["text" => "LaTeX", "callback_data" => "23"];
    $options[11][1] = ["text" => "Linux®", "callback_data" => "24"];
    $options[12][0] = ["text" => "MATLAB®", "callback_data" => "25"];
    $options[12][1] = ["text" => "Microsoft® SQL Server®", "callback_data" => "26"];
    $options[13][0] = ["text" => "MongoDB®", "callback_data" => "27"];
    $options[13][1] = ["text" => "MySQL®", "callback_data" => "28"];
    $options[14][0] = ["text" => "Node.JS®", "callback_data" => "29"];
    $options[14][1] = ["text" => "Objective-C®", "callback_data" => "30"];
    $options[15][0] = ["text" => "Oracle® Database", "callback_data" => "31"];
    $options[15][1] = ["text" => "Perl®", "callback_data" => "32"];
    $options[16][0] = ["text" => "PHP", "callback_data" => "33"];
    $options[16][1] = ["text" => "PostgreSQL®", "callback_data" => "34"];
    $options[17][0] = ["text" => "PowerShell®", "callback_data" => "35"];
    $options[17][1] = ["text" => "Python®", "callback_data" => "36"];
    $options[18][0] = ["text" => "R Programming", "callback_data" => "37"];
    $options[18][1] = ["text" => "React JS", "callback_data" => "38"];
    $options[19][0] = ["text" => "React Native", "callback_data" => "39"];
    $options[19][1] = ["text" => "Ruby®", "callback_data" => "40"];
    $options[20][0] = ["text" => "Ruby on Rails®", "callback_data" => "41"];
    $options[20][1] = ["text" => "Spring® Framework", "callback_data" => "42"];
    $options[21][0] = ["text" => "SQL", "callback_data" => "43"];
    $options[21][1] = ["text" => "Swift™", "callback_data" => "44"];
    $options[22][0] = ["text" => "TypeScript", "callback_data" => "45"];
    $options[22][1] = ["text" => "VBA", "callback_data" => "46"];
    $options[23][0] = ["text" => "Visual Basic® .NET", "callback_data" => "47"];
    $options[23][1] = ["text" => "Xamarin.Forms", "callback_data" => "48"];

    return $options;
}

function next_options()
{
    $options[0][0] = ["text" => "Next ▶️", "callback_data" => "Next"];
    $options[1][0] = ["text" => "🔙 Back", "callback_data" => "Back"];

    return $options;
}

function previous_options()
{
    $options[0][0] = ["text" => "◀️ Previous", "callback_data" => "Previous"];
    $options[1][0] = ["text" => "🔙 Back", "callback_data" => "Back"];

    return $options;
}

function next_previous()
{
    $options[0][0] = ["text" => "◀️ Previous", "callback_data" => "Previous"];
    $options[0][1] = ["text" => "Next ▶️", "callback_data" => "Next"];
    $options[1][0] = ["text" => "🔙 Back", "callback_data" => "Back"];

    return $options;
}

function coupon_next_options()
{
    $options[0][0] = ["text" => "Next ▶️", "callback_data" => "NextCoupon"];

    return $options;
}

function coupon_previous_options()
{
    $options[0][0] = ["text" => "◀️ Previous", "callback_data" => "PreviousCoupon"];

    return $options;
}

function coupon_next_previous()
{
    $options[0][0] = ["text" => "◀️ Previous", "callback_data" => "PreviousCoupon"];
    $options[0][1] = ["text" => "Next ▶️", "callback_data" => "NextCoupon"];

    return $options;
}
