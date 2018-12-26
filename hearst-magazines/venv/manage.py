from flask_script import Manager
from flask_migrate import Migrate, MigrateCommand
from app import app, db
from flask_sqlalchemy import SQLAlchemy

# db = SQLAlchemy()

manager = Manager(app)
migrate = Migrate(app, db)

manager.add_command('db', MigrateCommand)

if __name__ == '__main__':
    manager.run()
    db.create_all()
