var gulp = require('gulp');
var gutil = require('gulp-util');
var less = require('gulp-less');
var concat = require('gulp-concat');
var fixPath = require('./libs/fixPath.js');
var path = require('path');
var uglify = require('gulp-uglify');
var minifyCss = require('gulp-minify-css');
var handlebars = require('gulp-handlebars');
var defineModule = require('gulp-define-module');
var rebaseUrls = require('gulp-css-rebase-urls');
var gulpIf = require('gulp-if');

// config
var basePath, destPath;
basePath = destPath = '../../web/';

var scripts = require('./config/scripts.js');
var lessFiles = require('./config/less.js');
var handleBarFiles = [basePath + '**/*.handlebars'];
var customHandleBars = require('./config/handlebars.js');
var prod = false;

// callback for watch files
function watchFiles (type, key, files) {
    gulp.watch(files, function() {
        if (type == 'script') {
            buildScriptFile(key, files);
        } else if (type == 'less') {
            buildLessFile(key, files);
        } else if (type == 'handlebars') {
            buildHandlebarsFile(key, files);
        }
    });
}

// build js files
function buildScriptFile(dest, files) {
    gutil.log('Building ' + files.length + ' js files to ' + dest);

    gulp.src(files)
        .pipe(concat(path.basename(dest)))
        .pipe(gulpIf(prod, uglify()))
        .pipe(gulp.dest(path.dirname(destPath + dest)))
        ;
}

// build less files
function buildLessFile(dest, files) {
    gutil.log('Building ' + files.length + ' less files to ' + dest);

    gulp.src(files)
        .pipe(less())
        .pipe(rebaseUrls({root: path.dirname(basePath + dest)}))
        .pipe(concat(path.basename(dest)))
        .pipe(gulpIf(prod, minifyCss()))
        .pipe(gulp.dest(path.dirname(destPath + dest)))
        ;
}

// Convert full path to src scripts
function buildPathToHandlebars(files) {
    for (key in files) {
        files[key] = destPath + files[key];
    }
    return files;
}

function buildHandlebarsFile(id, files) {
    var dest = destPath + 'dist/js/hbs/';
    gutil.log('Building ' + files.length + ' handlebars files to ' + dest);
    gulp.src(files)
        .pipe(handlebars())
        .pipe(defineModule('plain', {
            wrapper: 'var templates = Handlebars.templates = Handlebars.templates || {};' +
                'templates["<%= name %>"] = <%= handlebars %>'
        }))
        .pipe(concat(id))
        .pipe(gulpIf(prod, uglify()))
        .pipe(gulp.dest(dest));
}

// build scripts
gulp.task('less', function() {
    gutil.log('Start building less');
    for (key in lessFiles) {
        buildLessFile(key, fixPath(lessFiles[key], basePath));
    }
});

// build scripts
gulp.task('scripts', function() {
    gutil.log('Start building scripts');
    for (key in scripts) {
        buildScriptFile(key, fixPath(scripts[key], basePath));
    }
});

// handlebars
gulp.task('templates', function() {
    gulp.src(handleBarFiles)
        .pipe(handlebars())
        .pipe(defineModule('plain', {
            wrapper: 'var templates = Handlebars.templates = Handlebars.templates || {};' +
                'templates["<%= name %>"] = <%= handlebars %>'
        }))
        .pipe(concat('templates.js'))
        .pipe(gulpIf(prod, uglify()))
        .pipe(gulp.dest(destPath + 'js/dist/hbs/'));
});

gulp.task('handlebars', function() {
    gutil.log('Start building handlebars');
    for (key in customHandleBars) {
        buildHandlebarsFile(key, buildPathToHandlebars(customHandleBars[key]));
    }
});

// default task
gulp.task('default', ['handlebars', 'templates', 'scripts', 'less']);

// watch
gulp.task('watch', ['watch-scripts', 'watch-less']);

// watch scripts
gulp.task('watch-scripts', ['scripts'], function() {
    for (key in customHandleBars) {
        // Build files
        buildHandlebarsFile(key, buildPathToHandlebars(customHandleBars[key]));
        // Watch files
        watchFiles('handlebars', key, buildPathToHandlebars(customHandleBars[key]));
    }

    // handlebars
    gulp.watch(handleBarFiles, ['templates']);

    // scripts
    for (key in scripts) {
        var files = fixPath(scripts[key], basePath);
        watchFiles ('script', key, files);
    }

});

// watch less
gulp.task('watch-less', ['less'], function() {
    // less
    for (key in lessFiles) {
        var files = fixPath(lessFiles[key], basePath);
        watchFiles ('less', key, files);
    }
});

gulp.task('set-prod', function() {
    prod = true;
});

gulp.task('set-prod-build', function() {
    destPath = '../../tmp_assetic/';
});

// prod build
gulp.task('prod', ['set-prod', 'default']);
gulp.task('prod-build', ['set-prod', 'set-prod-build', 'default']);