//
//  GULPFILE.JS - Reactified
//  Author: Nikolas Ramstedt (nikolas.ramstedt@helsingborg.se)
//
//  Commands:
//  "gulp"          -   Build and watch task combined
//  "gulp build"    -   Build assets
//  "gulp watch"    -   Watch for file changes and build changed files
//

const {
    series,
    src,
    dest,
    watch: gulpWatch
} = require('gulp');

const sassStep = require('gulp-sass');
const terser = require('gulp-terser');
const cleanCSS = require('gulp-clean-css');
const autoprefixer = require('gulp-autoprefixer');
const plumber = require('gulp-plumber');
const rev = require('gulp-rev');
const revDel = require('rev-del');
const sourcemaps = require('gulp-sourcemaps');
const notifier = require('node-notifier');
const del = require('del');

//Dependecies required to compile ES6 Scripts
const browserify = require('browserify');
const source = require('vinyl-source-stream');
const buffer = require('vinyl-buffer');
const babelify = require('babelify');

//Dependecies required to compile non React script
const concat = require('gulp-concat');
const uglify = require('gulp-uglify');
const jshint = require('gulp-jshint');

// ==========================================================================
// Build Tasks
// ==========================================================================

const build = series(
    cleanDist,
    sass,
    scripts,
    img,
    revision
);

const buildSass = series(sass, revision);
const buildScripts = series(scripts, revision);

// ==========================================================================
// Watch Task
// ==========================================================================

function watch() {
    gulpWatch(['source/js/**/*.js', 'source/js/**/*.jsx'], buildScripts);
    gulpWatch(['source/sass/**/*.scss'], buildSass);
    gulpWatch(['source/img/**/*.*'], buildImg);
}

// ==========================================================================
// SASS Task
// ==========================================================================

function sass() {
    const filePath = 'source/sass/';
    const tasks =
        ['event-manager-integration.scss', 'event-manager-integration-admin.scss']
            .map(fileName => new Promise(resolve => {
                src(filePath + fileName)
                .pipe(plumber())
                .pipe(sourcemaps.init())
                .pipe(
                    sassStep().on('error', function(err) {
                        console.log(err.message);
                        notifier.notify({
                            title: 'SASS Compile Error',
                            message: err.message,
                        });
                    })
                )
                .pipe(autoprefixer('last 2 version', 'safari 5', 'ie 8', 'ie 9', 'opera 12.1'))
                .pipe(sourcemaps.write())
                .pipe(dest('dist/css'))
                .pipe(cleanCSS({ debug: true }))
                .pipe(dest('dist/.tmp/css'))
                .on('end', resolve)
            }));
    return Promise.all(tasks);
}

// ==========================================================================
// Scripts Task
// ==========================================================================

function scripts() {
    const streams = [];

    streams.push(
        new Promise((resolve, reject) => {
            src(['source/js/admin/*.js'])
                .pipe(plumber())
                .pipe(sourcemaps.init())
                .pipe(jshint())
                .pipe(
                    jshint.reporter('fail').on('error', function(err) {
                        this.pipe(jshint.reporter('default'));
                        notifier.notify({
                            title: 'JS Compile Error',
                            message: err.message,
                        });
                    })
                )
                .pipe(concat('event-integration-admin.js'))
                .pipe(sourcemaps.write())
                .pipe(dest('dist/js'))
                .pipe(
                    uglify().on('error', function(err) {
                        return;
                    })
                )
                .pipe(dest('dist/.tmp/js'))
                .on('end', resolve)
                .on('error', reject);
        })
    )


    streams.push(
        new Promise((resolve, reject) => {
            src(['source/js/front/*.js'])
                .pipe(plumber())
                .pipe(sourcemaps.init())
                .pipe(jshint())
                .pipe(
                    jshint.reporter('fail').on('error', function(err) {
                        this.pipe(jshint.reporter('default'));
                        notifier.notify({
                            title: 'JS Compile Error',
                            message: err.message,
                        });
                    })
                )
                .pipe(concat('event-integration-front.js'))
                .pipe(sourcemaps.write())
                .pipe(dest('dist/js'))
                .pipe(
                    uglify().on('error', function(err) {
                        return;
                    })
                )
                .pipe(dest('dist/.tmp/js'))
                .on('end', resolve)
                .on('error', reject);
        })
    )

    streams.push(
        new Promise((resolve, reject) => {
            const filePath = 'source/js/';
            const file = 'Module/Event/Index.js';
            browserify({
                entries: [filePath + file],
                debug: true,
            })
                .transform([babelify])
                .bundle()
                .on('error', function(err) {
                    console.log(err.message);

                    notifier.notify({
                        title: 'Compile Error',
                        message: err.message,
                    });

                    this.emit('end');
                })
                .pipe(source(file)) // Converts To Vinyl Stream
                .pipe(buffer()) // Converts Vinyl Stream To Vinyl Buffer
                // Gulp Plugins Here!
                .pipe(sourcemaps.init())
                .pipe(sourcemaps.write())
                .pipe(dest('dist/js'))
                .pipe(terser())
                .pipe(dest('dist/.tmp/js'))
                .on('end', resolve)
                .on('error', reject);
        })
    )

    return Promise.all(streams);
}

// ==========================================================================
// Images
// ==========================================================================

const filePath = 'source/img/*.*';
function img() {
    return src(filePath)
        .pipe(dest('dist/img'));
}

// ==========================================================================
// Revision Task
// ==========================================================================

function revision() {
    return src('./dist/.tmp/**/*')
        .pipe(rev())
        .pipe(dest('./dist'))
        .pipe(rev.manifest(('rev-manifest.json', { merge: true }))) // ('rev-manifest.json', { merge: true })
        .pipe(revDel({ dest: './dist' }))
        .pipe(dest('./dist'));
}

// ==========================================================================
// Clean Task
// ==========================================================================

function cleanDist() {
    return new Promise(resolve => {
        del.sync('dist');
        resolve();
    });
}

// ==========================================================================
// Exports
// ==========================================================================

exports.build = build;
exports.buildSass = buildSass;
exports.buildScripts = buildScripts;
exports.default = series(build, watch);
