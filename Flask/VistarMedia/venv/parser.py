# Using Ray-casting algorithm
# Check if there are even number of horizontal intersections

from flask import Flask, json, render_template, request
from models import Polygon, Point, Segment

app = Flask(__name__)

@app.route('/', methods=['GET', 'POST'])
def parser():
    if request.method == 'POST':
        longitude = float(request.form['longitude'])
        latitude = float(request.form['latitude'])

        polygons = build_polygons()
        point = Point(longitude, latitude)
        result = search(polygons, point)

        return(result)

        # Following line is used for testing on web browser
        # return render_template("output.html", polygons=polygons)


def build_polygons():

    polygons = []

    with open("../static/states.json", "r") as json_data:
        for line in json_data:
            # result.append(json.loads(line))
            data = json.loads(line)
            poly = Polygon(data["state"])
            borders = data["border"]

            for border in borders:
                poly.add_point(Point(border[0], border[1]))

            polygons.append(poly)

    return polygons


def search(polygons, point):
    for polygon in polygons:
        if point_in_polygon(polygon, point):
            return polygon.state_name

        continue

    return "No State matches this point."


def point_in_polygon(polygon, point):
    line_left = Segment(Point(-999999999, point.y), point)
    line_right = Segment(point, Point(999999999, point.y))
    count_left = 0
    count_right = 0

    for e in polygon.get_edges():
        if edges_intersect(line_left, e):
            count_left += 1
        if edges_intersect(line_right, e):
            count_right += 1

    if count_left % 2 == 0 and count_right % 2 == 0:
        return False

    return True


def edges_intersect(e1, e2):
    A = e1.p1
    B = e1.p2
    C = e2.p1
    D = e2.p2

    return ccw(A, C, D) != ccw(B, C, D) and ccw(A, B, C) != ccw(A, B, D)


# Use counter clock wise comparison to check intersect
def ccw(A, B, C):
    return (C.y - A.y) * (B.x - A.x) > (B.y - A.y) * (C.x - A.x)
