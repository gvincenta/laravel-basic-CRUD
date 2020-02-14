import React, { useState, useEffect } from 'react';
import Axios from 'axios';
import {Table,Form,Button,Col,Row} from 'react-bootstrap';
import Item from './Item';
import Spinner from '../Spinner';
export default function Main(props) {
    //authors data:
    const [authors,setAuthors] = useState([]);
    //loading status:
    const [status,setStatus] = useState('');
    //id of the author to be changed:
    const [ID,setID] = useState(-1);
    //store old names to tell the users which author they are currently changing:
    const [oldLastName,setOLName]  = useState('');
    const [oldFirstName,setOFName] = useState('');
    //prompt user for the new author's first and last name:
    const [newFirstName,setNewFirstName] = useState('');
    const [newLastName,setNewLastName] = useState('');
    //success / failure message:
    const [message,setMessage] = useState('');
    // indicates if user has selected an author to be changed:
    const [clicked,setClicked] = useState(false);

    useEffect(() => {
        //firstly, fetch authors' data from backend:
        Axios.get('/api/authors')
            .then((res)=>{
                console.log(res, "Authors Table");

                setAuthors(res.data);

                setStatus("done");
                setMessage("");

            })

    }, [status])
    //display all authors after fetching in a table view:
    if (authors.length > 0 && status === "done"){
        return (
            <div>
                <Table striped bordered hover>
                    <thead>
                        <tr>
                        <th>ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Update</th>
                        </tr>
                    </thead>
                    <tbody>
                        {
                            authors.map(v => {
                                return <Item key={v.ID} author={v}
                                onClick={
                                    (ID,firstName,lastName) =>{
                                        //stores the current author to remind user which author they want to change:
                                    setOFName(firstName);
                                        setID(ID);
                                        setOLName(lastName);
                                    setClicked(true);
                                    }
                                }/>
                            })
                        }
                    </tbody>
                </Table>
        {//when user wants to change an author's name,show a form:
            clicked
            ?
        <Form onSubmit={
        (e)=>{
            //avoid reloading:
            e.preventDefault();
            //update author's name:
            Axios.put('/api/authors',{ID, firstName:newFirstName, lastName:newLastName})
                .then((res)=>
                    {console.log("res", res);
                        //TODO: HANDLE THIS LOGIC IN BACKEND
                        if (res.data.affectedRows == 1 ){
                            //sucessfully changed an author's name, re-fetch data again:
                            setMessage("succeed!");
                            setStatus("loading..");
                            window.location.reload();
                        } else{
                            setMessage("failed");
                        }
                    }
                )
        }
        }>
        <Form.Text className="text-muted">
            Changing Author with  ID : {ID} and name : {oldFirstName + ' '+ oldLastName }
        </Form.Text>
        <Row>
        <Col>
        <Form.Control type="string" placeholder="First Name" onChange={v => setNewFirstName(v.target.value)} alpha required />
        </Col>
        <Col>
        <Form.Control type="string" placeholder="Last Name" onChange={v => setNewLastName(v.target.value)} alpha required />
        </Col>
        </Row>

        <Button variant="primary" type="submit">
            Submit
            </Button>
            <Form.Text className="text-muted">
            {message}
            </Form.Text>
            </Form>
            : null

        }



             </div>
        );
    }

    return (<Spinner/>);
}
