# Biblioteca La Babel
*Biblioteca La Babel* (**BLB**) is a public-domained description dialects library for Online Judge problems powered by NOJ Babel.

## Structure
**BLB** uses hierarchical model for problems, each online judge have there own BABEL designations, thus have their own directory for problems.

|Online Judges|Directory|Problem Folder|
|-------------|---------|-------------|
|CodeForces|CodeForces|`CF1A`, `CF500A`|
|CodeForces Gym|Gym|`GYM100001A`|

Under each Online Judge directory, you can create a problem folder, following naming format above. For example, you can use `CodeForces/CF1A/` for problem `CF1A`.

Then under problem folder, you can specify a lang folder, of which uses language code, `zh-cn`, `en-us` or `klh`, etc.

Finally under lang folder you can create files that represents your translation for the problem:

|File Name|Role|
|---------|----|
|biblioteca.json| General Info |
|description.md| Description |
|input.md| Input |
|output.md| Output |
|note.md| Note |
