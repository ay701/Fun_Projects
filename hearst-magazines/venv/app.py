from flask_sqlalchemy import SQLAlchemy
from flask import Flask, render_template, request, session, Response, jsonify
from models import User, Course, Enrollment
from functools import wraps


db = SQLAlchemy()

app = Flask(__name__)
app.config['DEBUG'] = True
app.config['SQLALCHEMY_TRACK_MODIFICATIONS'] = False
app.config['SQLALCHEMY_DATABASE_URI'] = 'postgresql://localhost/hearst_magazines'
app.secret_key = b'\xf9\xca\xe6.\xfbvW\x15c\xc3\xd4\x1b\xba\x9bG$'
db.init_app(app)


# Request data type
def api_message():
    if request.headers['Content-Type'] == 'text/plain':
        return request.data
    elif request.headers['Content-Type'] == 'application/json':
        return jsonify(request.json)
    else:
        return "415 Unsupported Media Type ;)"


# Authorization
def check_auth(username, password):
    return username == 'admin' and password == 'secret'


def authenticate():
    message = {'message': "Authenticate."}
    resp = jsonify(message)

    resp.status_code = 401
    resp.headers['WWW-Authenticate'] = 'Basic realm="Example"'

    return resp


def requires_auth(f):
    @wraps(f)
    def decorated(*args, **kwargs):
        auth = request.authorization
        if not auth:
            return authenticate()

        elif not check_auth(auth.username, auth.password):
            return authenticate()
        return f(*args, **kwargs)

    return decorated


@app.errorhandler(404)
def not_found(error=None):
    message = {
            'status': 404,
            'message': 'Not Found: ' + request.url,
    }

    resp = jsonify(message)
    resp.status_code = 404

    return resp


# Student enroll a course
@app.route('/enroll', methods=['POST'])
def enroll():
    data = api_message()

    if User.query.filter_by(id=data.user_id).first() and \
            Course.query.filter_by(id=data.course_id).first():

        # Add enrollment entry to table
        new_enroll = Enrollment(data.user_id, data.course_id)
        db.session.add(new_enroll)
        db.session.commit()

        data = {
            'message': "Successfully enrolled the course."
        }

        resp = jsonify(data)
        resp.status_code = 201

        return resp
    else:
        return not_found()


# Add a course
@app.route('/course/add', methods=['POST'])
def add_course():
    data = api_message()

    # Only teacher can delete course
    if data.role == 'teacher':
        new_course = Course(data.user.id, data.title, data.start_date)
        db.session.add(new_course)
        db.session.commit()

        data = {
            'message': "Successfully added course."
        }

        resp = jsonify(data)
        resp.status_code = 201

        return resp
    else:
        return not_found()


# Delete a course
@app.route('/course/delete/<int:course_id>', methods=['DELETE'])
def delete_course(course_id):
    course = Course.query.filter_by(id=course_id).first()

    if course:
        db.session.delete(course)
        db.session.commit()

        data = {
            'message': "Successfully deleted course."
        }

        resp = jsonify(data)
        resp.status_code = 200
        return resp
    else:
        return not_found()


# Delete a student
@app.route('/student/delete/<int:user_id>', methods=['DELETE'])
def delete_student(user_id):
    student = User.query.filter_by(id=user_id).first()

    if student:
        db.session.delete(student)
        db.session.commit()

        data = {
            'message': "Successfully deleted student."
        }

        resp = jsonify(data)
        resp.status_code = 200
        return resp
    else:
        return not_found()


# Shows all students enrolled in a given course
@app.route('/students/course/<int:course_id>', methods=['GET'])
def students_by_course(course_id):
    user_ids = Enrollment.query.with_entities(Enrollment.user_id).filter_by(course_id=course_id)

    if user_ids:
        data = {
            'students': User.query.filter(User.id.in_(user_ids)).all(),
        }

        resp = jsonify(data)
        resp.status_code = 200

        return resp
    else:
        return not_found()


# Shows all courses enrolled by a student
@app.route('/courses/student/<int:user_id>', methods=['GET'])
def courses_by_user(user_id):
    course_ids = Enrollment.query.with_entities(Enrollment.course_id).filter_by(user_id=user_id)

    if course_ids:
        data = {
            'courses': Course.query.filter(Course.id.in_(course_ids)).all(),
        }

        resp = jsonify(data)
        resp.status_code = 200

        return resp
    else:
        return not_found()


# Search courses by title
@app.route('/courses/title/<string:title>', methods=['GET'])
def courses_by_title(title):
    courses = Course.query.filter(Course.title.like('%'+title+'%')).all()

    if courses:
        data = {
            'courses': courses,
        }

        resp = jsonify(data)
        resp.status_code = 200

        return resp
    else:
        return not_found()


# Search courses by start date
@app.route('/courses/start_date/<string:start_date>', methods=['GET'])
def courses_by_date(start_date):
    courses = Course.query.filter(Course.start_date.like('%' + start_date + '%')).all()

    if courses:
        data = {
            'courses': courses,
        }

        resp = jsonify(data)
        resp.status_code = 200

        return resp
    else:
        return not_found()


# User login verification
@app.route('/login', methods=['POST'])
def login():
    data = api_message()

    # Check if user email exists
    user = User.query.filter_by(email=data.email, password=data.password).first()

    if user:
        data = {
            'message': "Successfully logged in."
        }

        resp = jsonify(data)
        resp.status_code = 200

        return resp
    else:
        return not_found()


# New user sign up
@app.route('/register', methods=['POST'])
def register():
    data = api_message()

    # Check if email already used
    if not User.query.filter_by(email=data.email).first():
        new_user = User(data.first_name, data.last_name, data.password, data.gender, data.role, data.email)
        db.session.add(new_user)
        db.session.commit()

        data = {
            'message': "Successfully registered."
        }

        resp = jsonify(data)
        resp.status_code = 201

        return resp
    else:
        return not_found()


if __name__ == '__main__':
    app.debug = True
    app.run()
