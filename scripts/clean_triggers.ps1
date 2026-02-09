# clean_triggers.ps1
# Script para backup, listar y eliminar triggers en la tabla inventario de la BD rol

param(
    [string]$DBHost = "localhost",
    [string]$DBUser = "root",
    [string]$DBPass = "",
    [string]$DBName = "rol"
)

Write-Host "=== Limpieza de Triggers en inventario ===" -ForegroundColor Cyan
Write-Host ""

# 1. BACKUP
Write-Host "[OK] Haciendo backup de la BD $DBName..." -ForegroundColor Green
$backupFile = "C:\xampp\htdocs\DAW_EJERCICIOS\Pokemonrol\backups\rol_backup_$(Get-Date -Format 'yyyyMMdd_HHmmss').sql"
$backupDir = Split-Path $backupFile
if (!(Test-Path $backupDir)) { New-Item -ItemType Directory -Force -Path $backupDir | Out-Null }

try {
    & "C:\xampp\mysql\bin\mysqldump.exe" -u $DBUser "--password=$DBPass" $DBName | Out-File -Encoding ASCII $backupFile
    Write-Host "    Backup guardado en: $backupFile" -ForegroundColor Green
} catch {
    Write-Host "[ERROR] Error en backup: $_" -ForegroundColor Red
    exit 1
}

Write-Host ""

# 2. LISTAR TRIGGERS
Write-Host "[OK] Listando triggers en tabla inventario..." -ForegroundColor Green
$listQuery = "SELECT TRIGGER_NAME FROM INFORMATION_SCHEMA.TRIGGERS WHERE TRIGGER_SCHEMA='$DBName' AND EVENT_OBJECT_TABLE='inventario';"
$triggers = & "C:\xampp\mysql\bin\mysql.exe" -u $DBUser "--password=$DBPass" -D $DBName -e $listQuery --batch --skip-column-names

if ([string]::IsNullOrWhiteSpace($triggers)) {
    Write-Host "    No hay triggers en inventario. Perfecto!" -ForegroundColor Green
    Write-Host ""
    exit 0
}

Write-Host "    Triggers encontrados:" -ForegroundColor Yellow
$triggerList = @()
foreach ($line in $triggers.Split([Environment]::NewLine)) {
    if (-not [string]::IsNullOrWhiteSpace($line)) {
        Write-Host "      - $line"
        $triggerList += $line
    }
}

Write-Host ""

# 3. ELIMINAR TRIGGERS
Write-Host "[OK] Eliminando triggers..." -ForegroundColor Green
$allSuccess = $true
foreach ($trigger in $triggerList) {
    $dropQuery = "DROP TRIGGER IF EXISTS \`$trigger\`;"
    try {
        & "C:\xampp\mysql\bin\mysql.exe" -u $DBUser "--password=$DBPass" -D $DBName -e $dropQuery 2>&1 | Out-Null
        Write-Host "    Eliminado: $trigger" -ForegroundColor Green
    } catch {
        Write-Host "    Error al eliminar '$trigger': $_" -ForegroundColor Red
        $allSuccess = $false
    }
}

Write-Host ""

# 4. VERIFICACION
Write-Host "[OK] Verificando que no quedan triggers..." -ForegroundColor Green
$checkTriggers = & "C:\xampp\mysql\bin\mysql.exe" -u $DBUser "--password=$DBPass" -D $DBName -e $listQuery --batch --skip-column-names
if ([string]::IsNullOrWhiteSpace($checkTriggers)) {
    Write-Host "    Listo! No hay triggers en inventario." -ForegroundColor Green
} else {
    Write-Host "    Advertencia: aun hay triggers en inventario:" -ForegroundColor Yellow
    Write-Host $checkTriggers
    $allSuccess = $false
}

Write-Host ""
if ($allSuccess) {
    Write-Host "=== EXITO ===" -ForegroundColor Green
} else {
    Write-Host "=== COMPLETADO CON ADVERTENCIAS ===" -ForegroundColor Yellow
}
Write-Host ""
