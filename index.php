<?php

define('AWS_ACCESS_KEY', '');
define('AWS_SECRET', '');

$s3FormDetails = getS3Details('kieu-demo-pda-s3', 'ap-southeast-1');

function getS3Details($s3Bucket, $region, $acl = 'public-read', $folder = 'tinh/') {
    $date = gmdate('Ymd\THis\Z');
    $shortDate = gmdate('Ymd');
    $url = '//' . $s3Bucket . '.' . "s3" . '-' . $region . '.amazonaws.com';
    $scope = [
        AWS_ACCESS_KEY,
        $shortDate,
        $region,
        "s3",
        "aws4_request"
    ];
    $credentials = implode('/', $scope);
    $policy = [
        'expiration' => gmdate('Y-m-d\TG:i:s\Z', strtotime('+6 hours')),
        'conditions' => [
            ['bucket' => $s3Bucket],
            ['acl' => $acl],
            ['starts-with', '$key', ''],
            ['starts-with', '$Content-Type', ''],
            ['success_action_status' => '201'],
            ['x-amz-credential' => $credentials],
            ['x-amz-algorithm' => 'AWS4-HMAC-SHA256'],
            ['x-amz-date' => $date],
            ['x-amz-expires' => '86400'], // 24 Hours
        ]
    ];

    $base64Policy = base64_encode(json_encode($policy));

    $dateKey = hash_hmac('sha256', $shortDate, 'AWS4' . AWS_SECRET, true);
    $dateRegionKey = hash_hmac('sha256', $region, $dateKey, true);
    $dateRegionServiceKey = hash_hmac('sha256', 's3', $dateRegionKey, true);
    $signingKey = hash_hmac('sha256', 'aws4_request', $dateRegionServiceKey, true);
    $inputs = [
        'Content-Type' => '',
        'acl' => $acl,
        'success_action_status' => '201',
        'policy' => $base64Policy,
        'X-amz-credential' => $credentials,
        'X-amz-algorithm' => 'AWS4-HMAC-SHA256',
        'X-amz-date' => $date,
        'X-amz-expires' => '86400', // 24h
        'X-amz-signature' => hash_hmac('sha256', $base64Policy, $signingKey)
    ];
    return compact('url', 'inputs', 'folder');
}
?>

<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Upload Example</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/3.0.3/normalize.min.css">
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <div class="container">
        <h1>Direct Upload</h1>
        <form action="<?php echo $s3FormDetails['url']; ?>"
              method="POST"
              enctype="multipart/form-data"
              class="direct-upload">
            <?php foreach ($s3FormDetails['inputs'] as $name => $value) { ?>
                <input type="hidden" name="<?php echo $name; ?>" value="<?php echo $value; ?>">
            <?php } ?>
            <!-- Key is the file's name on S3 and will be filled in with JS -->
            <div id="folder" data-folder="<?php echo $s3FormDetails['folder']; ?>"></div>
            <input type="hidden" name="key" value="">
            <input type="file" name="file" multiple>
            <div class="progress-bar-area"></div>
        </form>

        <div>
            <h3>Files</h3>
            <textarea id="uploaded"></textarea>
        </div>
    </div>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
<!--         https://github.com/blueimp/jQuery-File-Upload -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/blueimp-file-upload/9.5.7/jquery.fileupload.js"></script>
        <script type="text/javascript" src="script.js"></script>
    </body>
</html>