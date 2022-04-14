$(document).ready(function () {
    var form = $('.direct-upload');
    var filesUploaded = [];
    form.fileupload({
        url: form.attr('action'),
        type: form.attr('method'),
        datatype: 'xml',
        add: function (event, data) {
            var file = data.files[0];
            var filename = Date.now() + '-' + file.name.split('.')[0] + '.' + file.name.split('.').pop();
            form.find('input[name="Content-Type"]').val(file.type);
            form.find('input[name="key"]').val($('#folder').data('folder') + filename);
            console.log("data", data);
            console.log("form", form);
            data.submit();
            var bar = $('<div class="progress" data-mod="' + file.size + '"><div class="bar"></div></div>');
            $('.progress-bar-area').append(bar);
            bar.slideDown('fast');
        },
        progress: function (e, data) {
            var percent = Math.round((data.loaded / data.total) * 100);
            $('.progress[data-mod="' + data.files[0].size + '"] .bar').css('width', percent + '%').html(percent + '%');
        },
        fail: function (e, data) {
            $('.progress[data-mod="' + data.files[0].size + '"] .bar').css('width', '100%').addClass('red').html('');
        },
        done: function (event, data) {
            var original = data.files[0];
            var s3Result = data.result.documentElement.children;
            filesUploaded.push({
                "original_name": original.name,
                    "s3_name": s3Result[2].innerHTML,
                    "size": original.size,
                    "url": s3Result[0].innerHTML
            });
            $('#uploaded').html(JSON.stringify(filesUploaded, null, 2));
        }
    });
});