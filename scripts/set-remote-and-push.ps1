# Usage (PowerShell, depuis le dossier du projet ou en passant le chemin) :
#   .\scripts\set-remote-and-push.ps1 -GitHubUser "ton_identifiant_github" -Repo "LudoProjet"
#
# Avant la première fois : crée le dépôt vide sur GitHub (sans README si tu pousses déjà du code).

param(
    [Parameter(Mandatory = $true)]
    [string] $GitHubUser,
    [string] $Repo = "LudoProjet"
)

$ErrorActionPreference = "Stop"
$repoRoot = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
Set-Location $repoRoot

$originUrl = "https://github.com/$GitHubUser/$Repo.git"
Write-Host "Remote origin -> $originUrl" -ForegroundColor Cyan

git remote remove origin 2>$null
git remote add origin $originUrl

git remote -v
Write-Host ""
Write-Host "Branche actuelle :" -ForegroundColor Yellow
git branch --show-current
Write-Host ""
Write-Host "Pour pousser :" -ForegroundColor Green
Write-Host "  git push -u origin main"
Write-Host ""
Write-Host "Si GitHub te demande un mot de passe, utilise un Personal Access Token (PAT), pas ton mot de passe GitHub."
Write-Host "https://github.com/settings/tokens"
