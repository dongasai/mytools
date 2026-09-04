# 


php artisan migrate:status

migrate:fresh                            Drop all tables and re-run all migrations
  migrate:install                          Create the migration repository
  migrate:refresh                          Reset and re-run all migrations
  migrate:reset                            Rollback all database migrations
  migrate:rollback                         Rollback the last database migration
  migrate:status                           Show the status of each migration


  php artisan module:migrate - 运行模块迁移
php artisan module:migrate-status - 查看模块迁移状态（需要指定模块名）
php artisan module:publish-migration - 发布模块迁移到主项目