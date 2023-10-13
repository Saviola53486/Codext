// Project Name
var project = 'kallyas-addon-nav-overlay';
var buildInclude 	= [
	'**/*.*',
	// exclude files and folders
	'!node_modules/**/*',
	// '!buildplugin/**/*',
	// '!**/*.map',
	'!' + project + '.zip'
];
var build = './buildplugin/';

//initialize all of our variables

var autoPrefixBrowserList = ['last 2 version', 'opera 12.1', 'ios 6', 'android 4', 'ie 11'];

//load all of our dependencies
//add more here if you want to include more libraries
var gulp        = require('gulp');
var gutil       = require('gulp-util');
var concat      = require('gulp-concat');
var uglify      = require('gulp-uglify');
var sass        = require('gulp-sass');
var cssnano 	= require('gulp-cssnano');
var sourceMaps  = require('gulp-sourcemaps');
var autoprefixer = require('gulp-autoprefixer');
var plumber     = require('gulp-plumber');
var base64     = require('gulp-base64');

var assetsDest = 'assets';

var srcInit = 'sass/app.scss';
var srcJS = ['assets/app.js'];

// Build:
var runSequence = require('run-sequence');
var notify       	= require('gulp-notify');
var zip 			= require('gulp-zip');
var del 	    	= require('del');


/**
 * ===============
 *     WATCH
 * ===============
 */

//compiling our SCSS files
gulp.task('styles', function() {
	//the initializer / master SCSS file, which will just be a file that imports everything
	return gulp.src(srcInit)
		.pipe(plumber({
		  errorHandler: function (err) {
			console.log(err);
			this.emit('end');
		  }
		}))
		.pipe(sourceMaps.init())
		.pipe(sass({
			  errLogToConsole: true,
			  includePaths: [
				  'sass/'
			  ]
		}))
		.pipe(autoprefixer({
		   browsers: autoPrefixBrowserList,
		   cascade:  true
		}))
		// .pipe( cssnano() )
		.on('error', gutil.log)
		.pipe(concat('styles.css'))
		.pipe(sourceMaps.write('.'))
		.pipe(gulp.dest(assetsDest));
});


//this is our master task when you run `gulp` in CLI / Terminal
//this is the main watcher to use when in active development
//  this will:
//  startup the web server,
//  start up browserSync
//  compress all scripts and SCSS files
gulp.task('default', ['styles'], function() {
	//a list of watchers, so it will watch all of the following files waiting for changes
	// gulp.watch(srcJS, ['scripts']);
	gulp.watch('sass/**', ['styles']);
});



/**
 * WORK IN PROGRESS
 */

/**
 * ===============
 *     DEPLOY
 * ===============
 * ===============
 */


//compiling our Javascripts for deployment
// gulp.task('scripts-deploy', function() {

// 	return gulp.src(['assets/js/vendor/**/*.js', 'assets/js/app.js'])
// 		.pipe(plumber())
// 		.pipe(concat('app.min.js'))
// 		.pipe(uglify())
// 		.on('error', gutil.log)
// 		.pipe(gulp.dest(assetsDest));
// });

//compiling our Javascripts
gulp.task('scripts-deploy', function() {
	return gulp.src(srcJS)
		.pipe(plumber())
		.pipe(concat('app.min.js'))
		.pipe(uglify())
		.on('error', gutil.log)
		.pipe(gulp.dest(assetsDest));
});

//compiling our SCSS files for deployment
gulp.task('styles-deploy', function() {

	return gulp.src(srcInit)
		.pipe(plumber())
		.pipe(sass({
			  includePaths: [
				  'sass/'
			  ]
		}))
		.pipe(autoprefixer({
		  browsers: autoPrefixBrowserList,
		  cascade:  true
		}))
		.pipe( base64({
            baseDir: 'assets/img',
            extensions: [/\.svg#datauri$/i, /\.png#datauri$/i, /\.jpg#datauri$/i],
            exclude:    [/\.server\.(com|net)\/dynamic\//, '--live.jpg'],
            maxImageSize: 8*1024, // bytes
            debug: true
        }) )
		.pipe(concat('styles.min.css'))
		.pipe( cssnano() )
		.pipe(gulp.dest(assetsDest));
});


/**
 * WORK IN PROGRESS
 */
//this is our deployment task, it will set everything for deployment-ready files
gulp.task('deploy', function(cb) {
	runSequence('styles','styles-deploy', 'scripts-deploy', cb);
});

/**
 * ===============
 *     BUILD
 * ===============
 */

gulp.task('buildFiles', function() {
	return 	gulp.src(buildInclude)
		 		.pipe(gulp.dest(build + project +'/'))
		 		.pipe(notify({ message: 'Copy from buildFiles complete', onLast: true }));



});

gulp.task('buildZip', function () {
	return 	gulp.src(build+'/**/')
	 		.pipe(zip(project+'.zip'))
	 		.pipe(gulp.dest('./'))
	 		.pipe(notify({ message: 'Zip task complete', onLast: true }));
});

gulp.task('cleanup', function(){
	return del(build);
});

gulp.task('build', function(cb) {
	runSequence('styles','styles-deploy', 'scripts-deploy', 'buildFiles', 'buildZip', 'cleanup', cb);
});