# State Server!

Vistar serves up a mound of geospatial data both internally and to third
parties. What we need is a server to tell us which state, if any, a point is in.
Some simplified geometries are included in states.json (so greatly simplified,
that some of the smaller ones disappear).

It need not be fast, but the code should be readable, and the results should be
correct.

## Expected Behavior

  $ ./state-server &
  [1] 21507
  $ curl  -d "longitude=-77.036133&latitude=40.513799" http://localhost:8080/
  ["Pennsylvania"]
  $


## Notes

This is your chance to shine! We are looking for quality code that really 
highlights your problem solving abilities and ability to engineer 
a clean, quality solution. While the coding challenge itself is not too 
technically difficult, delivering a quality solution might be--think: 
testing, quality, cleanliness, readability, maintainability, reuse, 
and performance. You're welcome to take the challenge as far as you want, 
but we're expecting something along those lines.

If there's anything special we have to do to run your program, just let us
know. A Makefile never hurt anyone.
