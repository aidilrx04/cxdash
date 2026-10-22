import 'dotenv/config';
import fs from 'fs';
import path from 'path';
import { execSync } from 'child_process';

const DEST_DIR = path.join( process.cwd(), 'installer-build' );

const essentialItems = [
	// Folders
	'app',
	{
		path: 'bootstrap',
		include: [ '.gitignore', 'app.php', 'providers.php' ]
	},
	'config',
	'database',
	{
		path: 'lib/pdfparser',
		ignore: [ "venv" ]
	},
	{
		path: 'lib/summarizer',
		ignore: [ "dist", "node_modules", "releases" ]
	},
	{
		path: 'public',
		ignore: [ 'storage' ]
	},
	'resources',
	'routes',
	{
		path: 'storage',
		include: [ '.gitignore' ]
	},

	// Files
	'artisan',
	'.env',
	'composer.json',
	'composer.lock',
	'install_services.bat',
	'stop_services.bat',
];

function buildInstaller()
{
	console.log( '🚀 Starting installer build preparation...' );

	// 0. Build frontend assets (Vite / Laravel Mix)[cite: 1]
	runNpmBuild();

	// 0.1. Build summarizer & generate release zip
	runSummarizerBuild();

	// 1. Clean up old build files if they exist[cite: 1]
	if ( fs.existsSync( DEST_DIR ) )
	{
		console.log( '🧹 Cleaning old files from target directory...' );
		fs.rmSync( DEST_DIR, { recursive: true, force: true } );
	}

	// 2. Ensure target directory exists[cite: 1]
	fs.mkdirSync( DEST_DIR, { recursive: true } );
	console.log( `📁 Created directory: ${DEST_DIR}` );

	// 3. Copy each essential item[cite: 1]
	copyEssentialFiles();

	// 3.1 Copy summarizer release.zip to root of installer-build
	copySummarizerReleaseZip();

	// 4. Copy portable Python distribution from INST_PYTHON env variable[cite: 1]
	copyPythonDependency();

	// 4.1 Set up python venv in staged pdfparser and install requirements
	setupPdfParserVenv();

	// 5. Copy portable PHP distribution from INST_PHP env variable
	copyPhpDependency();

	// 5.1 Copy NSSM binary distribution from INST_NSSM env variable
	copyNssmDependency();

	// 6. Run Composer install & dump-autoload inside installer-build[cite: 1]
	runComposerInstall();

	// 7. Copy and initialize MariaDB
	copyAndInitMariaDb();

	console.log( '\n✨ Laravel files and dependencies successfully staged in "installer-build"!' );
}

function runNpmBuild()
{
	console.log( '⚙️ Running npm run build...' );
	try
	{
		execSync( 'npm run build', {
			cwd: process.cwd(),
			stdio: 'inherit'
		} );
		console.log( '✅ Frontend assets built successfully!' );
	} catch ( err )
	{
		console.error( '❌ Failed to run npm run build:', err.message );
		process.exit( 1 );
	}
}

function runSummarizerBuild()
{
	console.log( '⚙️ Building lib/summarizer (npm run build)...' );
	const summarizerDir = path.join( process.cwd(), 'lib', 'summarizer' );
	if ( fs.existsSync( summarizerDir ) )
	{
		try
		{
			execSync( 'npm run build', {
				cwd: summarizerDir,
				stdio: 'inherit'
			} );
			console.log( '✅ Summarizer built successfully!' );
		} catch ( err )
		{
			console.error( '❌ Failed to build lib/summarizer:', err.message );
			process.exit( 1 );
		}
	} else
	{
		console.warn( '⚠️ Warning: lib/summarizer directory not found. Skipping build.' );
	}
}

function copyEssentialFiles()
{
	for ( const entry of essentialItems )
	{
		const itemName = typeof entry === 'string' ? entry : entry.path;
		const srcPath = path.join( process.cwd(), itemName );
		const destPath = path.join( DEST_DIR, itemName );

		if ( fs.existsSync( srcPath ) )
		{
			try
			{
				const cpOptions = {
					recursive: true,
					force: true
				};

				// Handle object filters (include or ignore rules)
				if ( typeof entry === 'object' )
				{
					cpOptions.filter = ( sourcePath ) =>
					{
						if ( sourcePath === srcPath ) return true;
						const relPath = path.relative( srcPath, sourcePath );
						const topLevelDir = relPath.split( path.sep )[ 0 ];

						if ( entry.ignore && entry.ignore.includes( topLevelDir ) )
						{
							return false;
						}

						if ( entry.include )
						{
							const stat = fs.statSync( sourcePath );
							if ( stat.isDirectory() ) return true;
							const baseName = path.basename( sourcePath );
							return entry.include.includes( baseName );
						}

						return true;
					};
				}

				fs.cpSync( srcPath, destPath, cpOptions );
				console.log( `✅ Copied: ${itemName}` );
			} catch ( err )
			{
				console.error( `❌ Failed to copy ${itemName}:`, err.message );
			}
		} else
		{
			console.warn( `⚠️ Warning: Essential item '${itemName}' not found in root directory. Skipping.` );
		}
	}
}

function copySummarizerReleaseZip()
{
	const zipSrc = path.join( process.cwd(), 'lib', 'summarizer', 'release', 'release.zip' );
	const zipDest = path.join( DEST_DIR, 'release.zip' );

	if ( fs.existsSync( zipSrc ) )
	{
		try
		{
			fs.copyFileSync( zipSrc, zipDest );
			console.log( '✅ Copied summarizer release.zip to installer-build root!' );
		} catch ( err )
		{
			console.error( '❌ Failed to copy release.zip:', err.message );
			process.exit( 1 );
		}
	} else
	{
		console.warn( '⚠️ Warning: lib/summarizer/release/release.zip not found. Skipping copy.' );
	}
}

function copyPythonDependency()
{
	const pythonSrc = process.env.INST_PYTHON;

	if ( !pythonSrc )
	{
		console.warn( '⚠️ Warning: INST_PYTHON is not defined in your .env file. Skipping Python dependency copy.' );
		return;
	}

	const resolvedPythonSrc = path.resolve( pythonSrc );
	const pythonDest = path.join( DEST_DIR, 'dependencies', 'python' );

	console.log( `🐍 Copying portable Python from: ${resolvedPythonSrc}...` );

	if ( fs.existsSync( resolvedPythonSrc ) )
	{
		try
		{
			fs.cpSync( resolvedPythonSrc, pythonDest, {
				recursive: true,
				force: true
			} );
			console.log( '✅ Python distribution copied successfully to dependencies/python!' );
		} catch ( err )
		{
			console.error( '❌ Failed to copy Python distribution:', err.message );
			process.exit( 1 );
		}
	} else
	{
		console.error( `❌ Error: The path specified in INST_PYTHON does not exist: ${resolvedPythonSrc}` );
		process.exit( 1 );
	}
}

function setupPdfParserVenv()
{
	const pdfParserDir = path.join( DEST_DIR, 'lib', 'pdfparser' );
	const requirementsPath = path.join( pdfParserDir, 'requirements.txt' );
	const pythonExe = path.join( DEST_DIR, 'dependencies', 'python', 'python', 'python.exe' );

	if ( !fs.existsSync( pdfParserDir ) )
	{
		console.log( 'ℹ️ Staged lib/pdfparser not found. Skipping virtual environment creation.' );
		return;
	}

	if ( !fs.existsSync( pythonExe ) )
	{
		console.warn( '⚠️ Warning: Portable python.exe not found in staged dependencies. Skipping venv setup for pdfparser.' );
		return;
	}

	console.log( '🐍 Creating virtual environment for lib/pdfparser...' );
	try
	{
		// 1. Create venv using portable Python
		execSync( `"${pythonExe}" -m venv venv`, {
			cwd: pdfParserDir,
			stdio: 'inherit'
		} );

		// 2. Install requirements if requirements.txt exists
		if ( fs.existsSync( requirementsPath ) )
		{
			console.log( '📦 Installing requirements for lib/pdfparser inside venv...' );
			const venvPip = path.join( pdfParserDir, 'venv', 'Scripts', 'pip.exe' );
			execSync( `"${venvPip}" install -r requirements.txt`, {
				cwd: pdfParserDir,
				stdio: 'inherit'
			} );
			console.log( '✅ Python virtual environment and requirements installed successfully!' );
		}
	} catch ( err )
	{
		console.error( '❌ Failed to configure python venv or install requirements for pdfparser:', err.message );
		process.exit( 1 );
	}
}

function copyPhpDependency()
{
	const phpSrc = process.env.INST_PHP;

	if ( !phpSrc )
	{
		console.warn( '⚠️ Warning: INST_PHP is not defined in your .env file. Skipping PHP dependency copy.' );
		return;
	}

	const resolvedPhpSrc = path.resolve( phpSrc );
	const phpDest = path.join( DEST_DIR, 'dependencies', 'php' );

	console.log( `🐘 Copying portable PHP from: ${resolvedPhpSrc}...` );

	if ( fs.existsSync( resolvedPhpSrc ) )
	{
		try
		{
			fs.cpSync( resolvedPhpSrc, phpDest, {
				recursive: true,
				force: true
			} );
			console.log( '✅ PHP distribution copied successfully to dependencies/php!' );
		} catch ( err )
		{
			console.error( '❌ Failed to copy PHP distribution:', err.message );
			process.exit( 1 );
		}
	} else
	{
		console.error( `❌ Error: The path specified in INST_PHP does not exist: ${resolvedPhpSrc}` );
		process.exit( 1 );
	}
}

function copyNssmDependency()
{
	const nssmSrc = process.env.INST_NSSM;

	if ( !nssmSrc )
	{
		console.warn( '⚠️ Warning: INST_NSSM is not defined in your .env file. Skipping NSSM copy.' );
		return;
	}

	const resolvedNssmSrc = path.resolve( nssmSrc );
	const nssmDest = path.join( DEST_DIR, 'dependencies', 'nssm' );

	console.log( `⚙️ Copying NSSM from: ${resolvedNssmSrc}...` );

	if ( fs.existsSync( resolvedNssmSrc ) )
	{
		try
		{
			fs.cpSync( resolvedNssmSrc, nssmDest, {
				recursive: true,
				force: true
			} );
			console.log( '✅ NSSM copied successfully to dependencies/nssm!' );
		} catch ( err )
		{
			console.error( '❌ Failed to copy NSSM:', err.message );
			process.exit( 1 );
		}
	} else
	{
		console.error( `❌ Error: The path specified in INST_NSSM does not exist: ${resolvedNssmSrc}` );
		process.exit( 1 );
	}
}

function runComposerInstall()
{
	console.log( '📦 Running composer install --no-dev inside "installer-build"...' );
	try
	{
		execSync( 'composer install --no-dev --optimize-autoloader', {
			cwd: DEST_DIR,
			stdio: 'inherit'
		} );
		console.log( '✅ Composer packages installed successfully!' );

		console.log( '🔄 Running composer dump-autoload --optimize...' );
		execSync( 'composer dump-autoload --optimize', {
			cwd: DEST_DIR,
			stdio: 'inherit'
		} );
		console.log( '✅ Autoloader dumped and optimized successfully!' );
	} catch ( err )
	{
		console.error( '❌ Failed during composer steps:', err.message );
		process.exit( 1 );
	}
}

function copyAndInitMariaDb()
{
	const mariaSrc = process.env.INST_MARIADB;

	if ( !mariaSrc )
	{
		console.warn( '⚠️ Warning: INST_MARIADB is not defined in your .env file. Skipping MariaDB copy.' );
		return;
	}

	const resolvedSrc = path.resolve( mariaSrc );
	const mariaDest = path.join( DEST_DIR, 'dependencies', 'mariadb' );
	const dataDir = path.join( mariaDest, 'data' );

	console.log( `🗄️ Copying portable MariaDB from: ${resolvedSrc}...` );

	if ( fs.existsSync( resolvedSrc ) )
	{
		try
		{
			// 1. Copy MariaDB distribution to installer-build/dependencies/mariadb
			fs.cpSync( resolvedSrc, mariaDest, {
				recursive: true,
				force: true
			} );

			// 2. Run mariadb-install-db.exe to initialize the data directory automatically
			console.log( '⚙️ Initializing MariaDB system tables and data directory...' );
			const installDbExe = path.join( mariaDest, 'bin', 'mariadb-install-db.exe' );

			execSync( `"${installDbExe}" --datadir="${dataDir}"`, {
				cwd: mariaDest,
				stdio: 'inherit'
			} );

			console.log( '✅ MariaDB initialized successfully!' );
		} catch ( err )
		{
			console.error( '❌ Failed to copy or initialize MariaDB:', err.message );
			process.exit( 1 );
		}
	} else
	{
		console.error( `❌ Error: The path specified in INST_MARIADB does not exist: ${resolvedSrc}` );
		process.exit( 1 );
	}
}

buildInstaller();