// Project Name
var project = 'znpb-flipbox';

var buildInclude 	= [
	'**/*.*',
	// exclude files and folders
	'!node_modules/**/*',
	// '!buildplugin/**/*',
	// '!**/*.map',
	'!' + project + '.zip'
];
var build = './buildplugin/';

// Build:
var notify       	= require('gulp-notify');
var zip 			= require('gulp-zip');
var del 	    	= require('del');
var runSequence 	= require('run-sequence');


var autoPrefixBrowserList = ['last 2 version', 'opera 12.1', 'ios 6', 'android 4', 'ie 10'];

//load all of our dependencies
//add more here if you want to include more libraries
var gulp        = require('gulp');
var gutil       = require('gulp-util');
var concat      = require('gulp-concat');
var sass        = require('gulp-sass');
var sourceMaps  = require('gulp-sourcemaps');
var autoprefixer = require('gulp-autoprefixer');
var plumber     = require('gulp-plumber');


var assetsDest = 'elements/flipbox';

var srcInit = 'sass/app.scss';

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
		.pipe(concat('style.css'))
		.pipe(sourceMaps.write('.'))
		.pipe(gulp.dest(assetsDest));
});

gulp.task('default', ['styles'], function() {
	//a list of watchers, so it will watch all of the following files waiting for changes
	gulp.watch('sass/**', ['styles']);
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
	runSequence('styles', 'buildFiles', 'buildZip', 'cleanup', cb);
});