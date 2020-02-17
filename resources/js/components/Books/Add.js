import React, { useState, useEffect } from 'react';
import {Button,Row,Col,ButtonGroup, Form,CardGroup,Card,ListGroup,ListGroupItem} from 'react-bootstrap';
import Axios from 'axios';
import nextId from "react-id-generator";
import AuthorList from './AuthorList';
import Autocomplete from './Autocomplete';
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

     const loading = authorsData.length===0
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
                    <Autocomplete data = {authorsData} loading = {loading} onChange={setCurrentAuthor}/>

                     <Button variant="primary"
                      onClick= {e =>
                        assignExistingAuthors([...existingAuthors,currentAuthor]) }>
                        Add
                     </Button>
                        </ButtonGroup>
                     <br/>
                        <AuthorList step={step} onNewAuthorRemove = {onNewAuthorRemove}
    onExistingAuthorRemove = {onExistingAuthorRemove} newAuthors = {newAuthors}
    existingAuthors = {existingAuthors}/>
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
                     <AuthorList step={step} onNewAuthorRemove = {onNewAuthorRemove}
                     onExistingAuthorRemove = {onExistingAuthorRemove} newAuthors = {newAuthors}
                     existingAuthors = {existingAuthors}/>





      <ButtonGroup>
     <Button variant="primary"onClick= {e => setStep(2)}> &lt; </Button>

     <Button variant="primary" type="submit"> Submit</Button>
     </ButtonGroup>
             </Form>
            )

     }


}


