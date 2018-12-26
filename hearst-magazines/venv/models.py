from flask_sqlalchemy import SQLAlchemy
from datetime import datetime

db = SQLAlchemy()


# Create users model
class User(db.Model):
    """Model for the users table"""
    __tablename__ = "users"

    id = db.Column(db.Integer, primary_key=True)
    first_name = db.Column(db.String(30))
    last_name = db.Column(db.String(30))
    gender = db.Column(db.String(10))
    email = db.Column(db.String(120), unique=True)
    password = db.Column(db.String(30))
    role = db.Column(db.String(30))  # student or teacher

    def __init__(self, first_name, last_name, password, gender, role, email):
        self.first_name = first_name
        self.last_name = last_name
        self.gender = gender
        self.role = role
        self.email = email
        self.password = password


# Create courses model
class Course(db.Model):
    """Model for the courses table"""
    __tablename__ = "courses"

    id = db.Column(db.Integer, primary_key=True)
    title = db.Column(db.String(120))
    start_date = db.Column(db.DateTime)
    user_id = db.Column(db.Integer)

    def __init__(self, title, start_date, user_id):
        self.title = title
        self.user_id = user_id
        self.start_date = start_date


# Create enrollments model
class Enrollment(db.Model):
    """Model for the enrollments table"""
    __tablename__ = "enrollments"

    id = db.Column(db.Integer, primary_key=True)
    course_id = db.Column(db.Integer)
    student_id = db.Column(db.Integer)
    enroll_date = db.Column(db.DateTime, default=datetime.utcnow)

    def __init__(self, course_id, student_id, enroll_date):
        self.course_id = course_id
        self.student_id = student_id
        self.enroll_date = enroll_date
