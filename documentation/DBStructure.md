# DB Structure Diagram 

![DB Structure](/DBStructure.png)

## Addition: 
1. To avoid spamming the authors_books table, a pair of `(authorID, bookID)` is set as an index called `Authorship` and is unique. 
2. *Anything that happens* to a `book` __will be cascaded__ in the `authors_books` table.
3. *To avoid complications,* an `author` __cannot be deleted__ as long as *they still have books assigned to them*.