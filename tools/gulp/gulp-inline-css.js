var gulp = require('gulp'),
    inlineCss = require('gulp-inline-css');

var args = process.argv.slice(2),
    htmlPath = args[0] || false,
    outputPath = args[1] || false

function callback(err) {
    if(err) {
        console.log('err');
    } else {
        console.log('end');
    }
}

if(htmlPath && outputPath) {
    gulp.src(htmlPath)
        .pipe(inlineCss({
            applyStyleTags: true,
            applyLinkTags: true,
            removeStyleTags: true,
            removeLinkTags: true
        }))
        .pipe(gulp.dest(outputPath))
        .on('end', function() {
            callback(null);
        })
        .on('error', function (err) {
            callback(err);
        });
}