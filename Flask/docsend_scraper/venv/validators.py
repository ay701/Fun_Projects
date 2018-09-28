from wtforms import Form, BooleanField, StringField, PasswordField, validators


class ScraperForm:
    def __init__(self):
        self.url = StringField("Link:", [
            validators.Regexp('^.*docsend.com', message="Link must be valid docsend.com URL"),
            validators.Length(min=4, max=250)
        ], render_kw={"placeholder": "enter link, i.e. https://docsend.com/view/p8jxsqr", "class": "form-control"})
        self.email_ad = StringField("Email Address:", [validators.Length(min=6, max=100)], render_kw={"placeholder": "enter email if needed ...", "class": "form-control"})
        self.email_pass = PasswordField("Docsend Password:", [], render_kw={"placeholder": "enter password if needed ...", "class": "form-control"})

