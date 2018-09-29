from flask import Flask, render_template, request, session, make_response
from flask import send_file
from selenium import webdriver
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.options import Options  
from selenium.webdriver.common.keys import Keys
from flask_bootstrap import Bootstrap
from fpdf import FPDF
from PIL import Image, ImageChops
from wtforms import Form, BooleanField, StringField, PasswordField, validators
from flask_socketio import SocketIO, emit, join_room, leave_room, \
    close_room, rooms, disconnect
from threading import Lock
from validate_email import validate_email

import os
import platform
import time
import random
import logging
import re


async_mode = None

# EB looks for an 'application' callable by default.
application = Flask(__name__)
bootstrap = Bootstrap(application)
application.config['SECRET_KEY'] = 'secret!'
socketio = SocketIO(application, async_mode=async_mode)
thread = None
thread_lock = Lock()


class ReadForm(Form):
    url = StringField("Link:", [
        validators.Regexp('^.*docsend.com', message="Link must be valid docsend.com URL"),
        validators.Length(min=4, max=250)
    ], render_kw={"placeholder": "enter link, i.e. https://docsend.com/view/p8jxsqr", "class": "form-control"})
    email_ad = StringField("Email Address:", [validators.Length(min=6, max=100)], render_kw={"placeholder": "enter email if needed ...", "class": "form-control"})
    email_pass = PasswordField("Docsend Password:", [], render_kw={"placeholder": "enter password if needed ...", "class": "form-control"})


def trim(im):
    bg = Image.new(im.mode, im.size, im.getpixel((50,50)))
    diff = ImageChops.difference(im, bg)
    diff = ImageChops.add(diff, diff, 2.0, -10)
    bbox = diff.getbbox()
    if bbox:
        return im.crop(bbox)


def refine_url(url):
    pos = url.find("docsend.com")
    return "https://" + url[pos:]


@application.route('/')
def index():
    form = ReadForm(request.form)
    return render_template('index.html', form=form, async_mode=socketio.async_mode)


# @application.route('/save_pdf', methods=['POST'])
@socketio.on('validate_request', namespace='/test')
def validate_request(message):

    valid = True

    regex = r"^.*docsend.com"
    match = re.search(regex, message['url'])
    if match is None:
        valid = False
        emit('url_response', {'message': 'Please enter valid Docsend Url.'})
    else:
        emit('url_response', {'message': ''})

    is_valid = validate_email(message['email_ad'])
    if not is_valid:
        valid = False
        emit('email_response', {'message': 'Please enter valid email address.'})
    else:
        emit('email_response', {'message': ''})

    if valid:
        session['url'] = refine_url(message['url'])
        session['email_ad'] = message['email_ad'].encode("ascii")
        session['email_pass'] = message['email_pass'].encode("ascii")
        emit('disable_load')


@socketio.on('start_process', namespace='/test')
def start_process():
    emit('save_pdf', {'message': 'Scraping in process ...'})


@socketio.on('save_pdf', namespace='/test')
def save_pdf():
    # emit('refresh_page')

    url = session['url']
    email_ad = session['email_ad']
    email_pass = session['email_pass']

    chrome_options = Options()
    chrome_options.add_argument("--headless")

    if platform.system() == "Darwin":
        browser = webdriver.Chrome(r'./chromedriver')
    else:
        browser = webdriver.Chrome(r'./chromedriver_linux', chrome_options=chrome_options)

    loc = str.find(url, "view")
    url_id = url[loc + 5:]
    session['url_id'] = url_id

    browser.get(url)
    time.sleep(2)

    try:
        element = WebDriverWait(browser, 4).until(
            EC.presence_of_element_located((By.ID, "youtube-modal"))
        )
    except Exception as e:
        print("failed")
        logging.exception(e)

    # Check if there's email & password input
    try:
        email_id = browser.find_element_by_name('visitor[email]')
        email_id.send_keys(email_ad)
    except Exception as e:
        print("no e-mail required")
        logging.exception(e)

    time.sleep(1)

    try:
        pass_id = browser.find_element_by_name('visitor[passcode]')
        pass_id.send_keys(email_pass)
    except Exception as e:
        print("no password required")
        logging.exception(e)

    time.sleep(1)

    try:
        email_id = browser.find_element_by_name('visitor[email]')
        email_id.send_keys(Keys.TAB)
        email_id.send_keys(Keys.ENTER)
    except Exception as e:
        print("ae")
        logging.exception(e)

    exit_flag = 0
    browser.switch_to_active_element().send_keys(Keys.RIGHT)

    while exit_flag == 0:
        # click right until no blank pagescheck until pages stabilizes
        browser.switch_to_active_element().send_keys(Keys.RIGHT)
        time.sleep(0.4 + random.randint(1, 100) / 100)
        pages = browser.find_elements_by_css_selector(".preso-view.page-view")
        urls = []
        for x in pages:
            urls.append(x.get_attribute("src"))

        if any("blank.gif" in s for s in urls):
            exit_flag = 0
        else:
            exit_flag = 1

    urls = []
    for x in pages:
        urls.append(x.get_attribute("src"))

    c = 1
    for x in urls:
        browser.get(x)
        browser.save_screenshot("AX" + str(c) + ".png")
        c = c + 1

    image_list = []
    for i in range(1, c):
        image_list.append("AX" + str(i) + ".png")

    session['image_list'] = image_list
    time.sleep(5)
    for ind, img in enumerate(image_list):
        im = Image.open(img)
        im = trim(im)
        im.save(img)

    im = Image.open(image_list[0])
    w_height = im.size[0]
    w_width = im.size[1]
    im.close()

    pdf = FPDF("L", "pt", [w_width, w_height])
    pdf.set_margins(0, 0, 0)

    for image in image_list:
        pdf.add_page()
        pdf.image(image, 0, 0)

    for i in (image_list):
        os.remove(i)

    browser.close()
    time.sleep(3)

    # now serve the PDF
    response = make_response(pdf.output(dest='S'))
    response.headers.set('Content-Disposition', 'attachment', filename=url_id + '.pdf')
    response.headers.set('Content-Type', 'application/pdf')

    return response

    emit('refresh_button', {'message': 'Complete!'})

def background_thread():
    """Example of how to send server generated events to clients."""
    count = 0
    while True:
        socketio.sleep(5)
        count += 1


@socketio.on('connect', namespace='/test')
def test_connect():
    global thread
    with thread_lock:
        if thread is None:
            thread = socketio.start_background_task(target=background_thread)


# run the app.
if __name__ == "__main__":
    # Setting debug to True enables debug output. This line should be
    # removed before deploying a production app.
    application.debug = True
    socketio.run(application)
