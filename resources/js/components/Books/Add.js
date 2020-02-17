import React, { useState, useEffect } from 'react';
import {TextField} from '@material-ui/core';
import {Autocomplete} from '@material-ui/lab';
import {Button,Row,Col,ButtonGroup, Form,CardGroup,Card,ListGroup,ListGroupItem} from 'react-bootstrap';
import Axios from 'axios';
import Item from './Item';
import nextId from "react-id-generator";


export default function (props) {
     //authors data from backend:
     const [authorsData,setAuthorsData] = useState([]);
     //UI filling form step (1, 2, 3):
     const [step,setStep] = useState(1);
     //the new book's title:
     const [title,setTitle] = useState([]);
     //existing authors to be assigned to the new book:
     const [existingAuthors,assignExistingAuthors] = useState([]);
     //new (i.e. non-existing authors) to be assigned to the new book:
     const [newAuthors,assignNewAuthors] = useState([]);
     //currently selected existing author:
     const [currentAuthor,setCurrentAuthor] = useState({});
     //currently entered new author (need their first and last name):
     const [firstName,setFirstName] = useState('');
     const [lastName,setLastName] = useState('');
     /*removing item from array adapted from :
     https://stackoverflow.com/questions/57341541/removing-object-from-array-using-hooks-usestate
      */
     const onExistingAuthorRemove = (removeID)=>{
            console.log(removeID, "ID RECORDED");
            assignExistingAuthors(existingAuthors.filter(item =>  item.ID !== removeID));


     }
     //for new authors, as they don't have an ID, we assign fakeID by nextId() for removal purposes only:
    const onNewAuthorRemove = (removeID)=>{
        assignNewAuthors(newAuthors.filter(item => item.ID !== removeID));

    }

     useEffect(()=>{
        Axios.get('/api/books')
            .then((res) => {
                console.log("Main",res);
                setAuthorsData(res.data);
                setStatus("done");
            });
    },[status]);
     //TODO : refactor reusable components + allow removing authors from the list!!
     switch (step) {
         //step 1: enter book's title:
         case 1:
             return (
                 <Form>
                     <Form.Group controlId="bookTitle">
                         <h2>Book Title</h2>
                        <Form.Control type="text" placeholder="Please enter the book's title" required
                        onChange = {e => setTitle(e.target.value)}/>

                     </Form.Group>
                    <Button variant="primary" onClick= {e => setStep(2)}>
                        &gt;
                    </Button>
                 </Form>
             )
        //step 2 : assign existing authors to this new book:
         case 2:
             return(
                 <div>
                     <h2>Assign existing authors to {title} </h2>
                        <br/>
                    <ButtonGroup>
                     <Autocomplete
                     id="combo-box-demo"
                     options={authorsData}
                     getOptionLabel={option => {return option.ID + " " +  option.firstName + " " + option.lastName}}
                     style={{ width: 300 }}

                     renderInput={params => (
                        <TextField {...params} label="Existing Authors" variant="outlined" fullWidth />
                        )}
                     onChange = { event => {
                         console.log("ON CHANGE",event.target.value);
                         setCurrentAuthor(extractAuthor(event.target.innerHTML));
                        }
                     }
                     />
                     <Button variant="primary"
                      onClick= {e =>
                        assignExistingAuthors([...existingAuthors,currentAuthor]) }>
                        Add
                     </Button>
                        </ButtonGroup>
                     <br/>
                     <CardGroup>
                         <Card>
                            <Card.Body>
                                <Card.Title>Assigned existing authors: </Card.Title>
                            </Card.Body>
                            <ListGroup>
                                {loop(existingAuthors,onExistingAuthorRemove,true)}
                            </ListGroup>
                         </Card>
                     </CardGroup>
                     <ButtonGroup>
                         <Button variant="primary"onClick= {e => setStep(1)}> &lt; </Button>

                         <Button variant="primary" onClick= {e => setStep(3)}> &gt;</Button>
                         </ButtonGroup>



                </div>
            )
        //step 3 : assign new authors to this new book:
         case 3:
             return (
                 <Form
                  onSubmit = {
                     e =>{
                        e.preventDefault();

                        console.log(existingAuthors,"existingAuthors");
                        Axios.post("/api/books",
                            {
                                authors : existingAuthors,
                                newAuthors,
                                title
                            })
                            .then(res => {
                                console.log(res, "RES");
                            })
                    }} >
                     <h2>Assign new authors to {title}</h2>
                     <Row>
                         <Col sm="5">
                            <Form.Control type="text" placeholder="First Name" onChange={v => setFirstName(v.target.value)}   />
                         </Col>
                         <Col sm="5">
                            <Form.Control type="text" placeholder="Last Name" onChange={v => setLastName(v.target.value)}   />
                         </Col>
                         <Col>
                            <Button variant="primary"
                            onClick= {e =>

                            assignNewAuthors([...newAuthors,{ ID : nextId(), firstName,lastName}])
                        }>
                            Add
                            </Button>
                         </Col>
                     </Row>




                     <CardGroup>
                         <Card>
                             <Card.Body>
                                <Card.Title>Assigned new authors: </Card.Title>
                             </Card.Body>
                             <ListGroup>
                                {loop(newAuthors,onNewAuthorRemove,false)}
                             </ListGroup>
                         </Card>
                         <Card>
                             <Card.Body>
                                <Card.Title>Assigned existing authors: </Card.Title>
                             </Card.Body>
                             <ListGroup>
                                {loop(existingAuthors,onExistingAuthorRemove)}
                             </ListGroup>
                         </Card>
                     </CardGroup>
      <ButtonGroup>
     <Button variant="primary"onClick= {e => setStep(2)}> &lt; </Button>

     <Button variant="primary" type="submit"> Submit</Button>
     </ButtonGroup>
             </Form>
            )

     }


}
function extractAuthor(authorString) {
     console.log(authorString,"authorString")
    console.log(typeof authorString,"authorString")

    var author = authorString.split(" ");
    return {"ID" : author[0],"firstName" : author[1], "lastName" : author[2]};
}
function loop(x,onRemove,showID) {

    var array = [];
    for (var i = 0 ; i < x.length; i++){
        array.push(  React.createElement(
            Item,
            {author: x[i], key:i, index: i, onClick:onRemove, showID}
        ))
    }
    return array;

}
