const { app, BrowserWindow, dialog } = require('electron');
const { spawn } = require('child_process');
const fs = require('fs');
const path = require('path');

const BACKEND_DIR = path.join(__dirname, 'backend');
const APP_URL = 'http://127.0.0.1:8000';

let mainWindow = null;
let phpServer = null;

function exists(filePath) {
  return fs.existsSync(filePath);
}

function ensureBackendPrerequisites() {
  const artisanPath = path.join(BACKEND_DIR, 'artisan');
  if (!exists(artisanPath)) {
    throw new Error('Missing backend/artisan. Make sure Laravel files were copied into backend/.');
  }

  const envPath = path.join(BACKEND_DIR, '.env');
  const envExamplePath = path.join(BACKEND_DIR, '.env.example');

  if (!exists(envPath) && exists(envExamplePath)) {
    fs.copyFileSync(envExamplePath, envPath);
  }
}

function startLaravelServer() {
  return new Promise((resolve, reject) => {
    phpServer = spawn(
      'php',
      ['artisan', 'serve', '--host=127.0.0.1', '--port=8000'],
      {
        cwd: BACKEND_DIR,
        windowsHide: true,
        shell: true
      }
    );

    phpServer.on('error', (error) => {
      reject(new Error(`Unable to start PHP server: ${error.message}`));
    });

    let settled = false;

    const markReady = (chunk) => {
      const line = chunk.toString();
      if (!settled && line.includes('Server running on')) {
        settled = true;
        resolve();
      }
    };

    phpServer.stdout.on('data', markReady);
    phpServer.stderr.on('data', markReady);

    phpServer.on('exit', (code) => {
      if (!settled) {
        settled = true;
        reject(new Error(`PHP server exited before startup. Exit code: ${code}`));
      }
    });
  });
}

async function createMainWindow() {
  mainWindow = new BrowserWindow({
    width: 1440,
    height: 900,
    minWidth: 1024,
    minHeight: 700,
    autoHideMenuBar: true,
    webPreferences: {
      contextIsolation: true,
      sandbox: true
    }
  });

  // Clear persisted browser state to avoid stale CSRF/session cookies in desktop shell.
  const browserSession = mainWindow.webContents.session;
  await browserSession.clearStorageData({
    storages: ['cookies', 'localstorage', 'indexdb', 'serviceworkers', 'cachestorage']
  });
  await browserSession.clearCache();

  await mainWindow.loadURL(APP_URL);
}

async function bootstrap() {
  try {
    ensureBackendPrerequisites();
    await startLaravelServer();
    await createMainWindow();
  } catch (error) {
    dialog.showErrorBox('Aeternitas Desktop Startup Error', `${error.message}\n\nInstall requirements:\n1) PHP in PATH\n2) backend dependencies (composer install and npm install)`);
    app.quit();
  }
}

app.whenReady().then(bootstrap);

app.on('window-all-closed', () => {
  if (phpServer && !phpServer.killed) {
    phpServer.kill();
  }

  if (process.platform !== 'darwin') {
    app.quit();
  }
});

app.on('before-quit', () => {
  if (phpServer && !phpServer.killed) {
    phpServer.kill();
  }
});
