# XML Export Method (Handled In UtilityController) 
####  The structure we are trying to export is (i.e. the input to exportToXML in UtilityController):  
```
[
    {
        "bookID":22, 
        "title":"HALLO GILBERT ONLY1", 
        "authors": [{"authorID":2,"firstName":"Gilbert","lastName":"Vincenta”}]     
    }, 
 ... 
]
```
#### Into:  
```
<?xml version="1.0"?> 
<books> (0)  
    <data> (1) 
        <bookID>22</bookID> (2) 
        <title>HALLO GILBERT ONLY 1</title> (3) 
        <authors> (4) 
            <data> (5) 
                <authorID>2</authorID> (6) 
                <firstName>Gilbert</firstName> (7) 
                <lastName>Vincenta</lastName> (8) 
            </data>  
        </authors> (9) 
    </data> (10)  
    …
</books> 
```

## Notice that:  
1. `$rootTag` :  root element tag, *in this case is `books`*. 
2. `$nestedTag` :  is used to check whether the JSON contains `"authors": [{...}] ` that needs to be parsed  into ` <authors> <data> …. </data> </authors>` (a.k.a `child objects`) as shown above.  *(in this case is `authors`)*

## The algorithm:  
1. Initialise XML root tag (this step is done in `exportToXML`).  
    *For each JSON element found in the array:*  
2.  (this step is done in constructChild function: 
    1. If the object has  `"authors": [ ... ] `
        1. Parse  from `(1) to (3)` 
        2. Repeat 2(i) for  `"authors": [ ... ] `. __Notice that as nesting only occurs once,__ *i.e. the child object doesn’t have another potential grandchild objects to be parsed* like `<authors> <data> …. </data> </authors>`, so, *the recursion is  useless for now* __(but might be useful in the future)__, and in this stage, `(4) to (9)` will be parsed into XML. 
        3. Then, parse `(10)`.  
        4. Go to step 4.  
    2. If the object has no highlighted data 
        1. go to step 3.  
    3. Parse the data from `(1) to (10)`. Note that as the object has no highlighted data, `(4) to (9)` would not appear in this case. 
    4. Continue doing this until all JSON elements have been looped through.  

## More on the technical side:
1. For Books  / Authors only XML, the procedure is the same. In these 2 cases, `(4) to (9)` will always be missing.  
2. The function needs to know how to retrieve the `"authors": [ ... ] `  by specifying its key, a.k.a `$childKeys` in the export. 
3. The function needs to know what `(0) and (4)` are, specified in `$nestedTags`.   
4. The function needs to know what tags go into `(2),(3),(6),(7),(8)` through the `$attributes`. 
5. *Testing this output is handled in similar fashion.* 
6. There are 2 versions of Authors and Books XML export:
    1. Route: `api/authors/export/XML/with-books` as shown in  _Figure 2_ below.
    2. Route: `api/books/export/XML/with-authors` as shown in _Figure 1_ below.
    3. __For simplicity on the frontend, `api/books/export/XML/with-authors`  _(Figure 1)_ isn’t used,__ but is maintained *in case it is needed in the future*. 

###### Figure 1: Sample response from:  api/authors/export/XML/with-books
```        
<?xml version="1.0"?> 
    <books>   
        <data>     
            <bookID>22</bookID>    
            <title>Hello World</title>     
            <authors>       
                <data>         
                    <authorID>2</authorID>         
                    <firstName>Gilbert</firstName>         
                    <lastName>Vincenta</lastName>       
                </data>    
            </authors>  
        </data>
    </books>  
```
###### Figure 2: Sample response from:  api/books/export/XML/with-authors 
```
<?xml version="1.0"?> 
    <authors>   
        <data>     
            <authorID>2</authorID>     
            <firstName>Gilbert</firstName>     
            <lastName>Vincenta</lastName>     
            <books>       
                <data>         
                    <bookID>22</bookID>         
                    <title>Hello World</title>       
                </data>    
            </books>
        </data> 
    </authors> 
```
 
 