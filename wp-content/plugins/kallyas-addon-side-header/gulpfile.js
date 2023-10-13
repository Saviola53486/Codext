// Project Name
var project = 'kallyas-addon-side-header';
var buildInclude 	= [
	'**/*.*',
	// exclude files and folders
	'!node_modules/**/*',
	// '!buildplugin/**/*',
	// '!**/*.map',
	'!' + project + '.zip'
];
var build = './buildplugin/';

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
// var gulpSequence = require('gulp-sequence').use(gulp);
var runSequence = require('run-sequence');
var plumber     = require('gulp-plumber');
var base64     	= require('gulp-base64');

// Build:
var notify       	= require('gulp-notify');
var zip 			= require('gulp-zip');
var del 	    	= require('del');

// Paths
var assetsDest = 'assets';
var srcInit = 'sass/app.scss';
var srcJS = ['assets/app.js'];

// Params
var autoPrefixParams = {
	browsers: ['last 2 version', 'opera 12.1', 'ios 6', 'android 4', 'ie 11'],
	cascade:  true
};

/**
 * ===============
 *     WATCH
 * ===============
 */

//compiling our SCSS files
gulp.task('styles', function() {

	return gulp.src(srcInit)
		.pipe(plumber({
		  errorHandler: function (err) {
			console.log(err);
			this.emit('end');
		  }
		}))
		.pipe(sourceMaps.init())
		.pipe( sass({
			errLogToConsole: true,
			includePaths: [
				'sass/'
			]
		}) )
		.pipe( autoprefixer(autoPrefixParams) )
		.pipe(concat('styles.css'))
		.pipe(sourceMaps.write('.'))
		.on('error', gutil.log)
		.pipe(gulp.dest(assetsDest));
});

gulp.task('default', ['styles'], function() {
	//a list of watchers, so it will watch all of the following files waiting for changes
	// gulp.watch(srcJS, ['scripts']);
	gulp.watch('sass/**', ['styles']);
});


/**
 * ===============
 *     DEPLOY
 * ===============
 */

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
gulp.task('styles-min', function() {

	return gulp.src(srcInit)
		.pipe(plumber())
		.pipe( sass({
			includePaths: [
				'sass/'
			]
		}) )
		.pipe( autoprefixer(autoPrefixParams) )
		.pipe( base64({
			baseDir: 'assets',
			extensions: [/\.svg#datauri$/i, /\.png#datauri$/i, /\.jpg#datauri$/i, 'woff', 'ttf', 'eot', 'svg'],
			exclude:    [/\.server\.(com|net)\/dynamic\//, '--live.jpg', 'map'],
			maxImageSize: 8*1024, // bytes
			debug: true
		}) )
		.pipe( concat('styles.min.css') )
		.pipe( cssnano() )
		.pipe(gulp.dest(assetsDest));
});


//this is our deployment task, it will set everything for deployment-ready files
// gulp.task('deploy', runSequence([ 'styles', 'styles-min', 'scripts-deploy']) );

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
	runSequence('styles', 'styles-min', 'scripts-deploy', 'buildFiles', 'buildZip', 'cleanup', cb);
});