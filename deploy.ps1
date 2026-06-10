param(
    [string]$EnvFile = ".env.deploy",
    [string]$WinScpPath = "WinSCP.com"
)

$ErrorActionPreference = "Stop"
$RootDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$EnvPath = Join-Path $RootDir $EnvFile

if (-not (Test-Path $EnvPath)) {
    throw "Missing $EnvPath"
}

$DeployEnv = @{}
Get-Content $EnvPath | ForEach-Object {
    $line = $_.Trim()
    if ($line -eq "" -or $line.StartsWith("#")) {
        return
    }

    $parts = $line.Split("=", 2)
    if ($parts.Count -eq 2) {
        $DeployEnv[$parts[0].Trim()] = $parts[1].Trim()
    }
}

foreach ($key in @("FTP_HOST", "FTP_USER", "FTP_PASS", "FTP_PORT", "FTP_REMOTE_DIR")) {
    if (-not $DeployEnv.ContainsKey($key) -or [string]::IsNullOrWhiteSpace($DeployEnv[$key])) {
        throw "Missing $key in $EnvPath"
    }
}

$HostName = $DeployEnv["FTP_HOST"]
$UserName = $DeployEnv["FTP_USER"]
$Password = $DeployEnv["FTP_PASS"]
$Port = $DeployEnv["FTP_PORT"]
$RemoteDir = $DeployEnv["FTP_REMOTE_DIR"]
$LogPath = Join-Path $RootDir "deploy-winscp.log"
$ScriptPath = Join-Path $env:TEMP "mttq-winscp-deploy.txt"

$FileMask = "| .git/; node_modules/; vendor/; .env; .env.deploy; storage/logs/; tests/; *.log; .DS_Store"
$WinScpCommands = @(
    "option batch abort",
    "option confirm off",
    "open ftp://$($UserName):$($Password)@$($HostName):$($Port)/",
    "synchronize remote -criteria=time -filemask=`"$FileMask`" `"$RootDir`" `"$RemoteDir`"",
    "exit"
)

$WinScpCommands | Set-Content -Path $ScriptPath -Encoding ASCII

Write-Host "Deploying $RootDir to ftp://$HostName`:$Port$RemoteDir"
Write-Host "This upload does not delete remote files."

& $WinScpPath /script="$ScriptPath" /log="$LogPath"

if ($LASTEXITCODE -ne 0) {
    throw "WinSCP deploy failed. See $LogPath"
}

Write-Host "Deploy finished."
