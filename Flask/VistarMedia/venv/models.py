# All classes stored here

class Point:
    x = None
    y = None

    def __init__(self, x, y):
        self.x = x
        self.y = y


class Segment:
    p1 = None
    p2 = None

    def __init__(self, p1, p2):
        self.p1 = p1
        self.p2 = p2


class Polygon:
    points = None

    def __init__(self, state_name):
        self.points = []
        self.state_name = state_name

    def add_point(self, p):
        self.points.append(p)

    def get_edges(self):
        edges = []

        for i in range(len(self.points)):
            if i == len(self.points) - 1:
                j = 0
            else:
                j = i + 1
            edges.append(Segment(self.points[i], self.points[j]))

        return edges
