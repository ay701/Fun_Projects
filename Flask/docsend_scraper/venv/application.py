from flask import Flask, render_template, request, make_response
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

import os
import platform
import time
import random
import logging

# EB looks for an 'application' callable by default.
application = Flask(__name__)
bootstrap = Bootstrap(application)


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
    return render_template('index.html', form=form)


@application.route('/save_pdf', methods=['POST'])
def save_pdf():

    form = ReadForm(request.form)

    if form.validate():
        # Check if it exists
        url = refine_url(form.url.data)
        email_ad = form.email_ad.data.encode("ascii")
        email_pass = form.email_pass.data.encode("ascii")

        chrome_options = Options()
        chrome_options.add_argument("--headless")

        if platform.system() == "Darwin":
            browser = webdriver.Chrome(r'./chromedriver')
        else:
            browser = webdriver.Chrome(r'./chromedriver_linux', chrome_options=chrome_options)

        loc = str.find(url, "view")
        ID = url[loc + 5:]

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

        time.sleep(10)
        for img in image_list:
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

        # now serve the PDF
        response = make_response(pdf.output(dest='S'))
        response.headers.set('Content-Disposition', 'attachment', filename=ID + '.pdf')
        response.headers.set('Content-Type', 'application/pdf')
        return response

    return render_template('index.html', form=form)


# run the app.
if __name__ == "__main__":
    # Setting debug to True enables debug output. This line should be
    # removed before deploying a production app.
    application.debug = True
    application.run(host='0.0.0.0')