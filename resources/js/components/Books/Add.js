import React, { useState, useEffect } from 'react';
import { Button, Row, Col, ButtonGroup, Form } from 'react-bootstrap';
import Axios from 'axios';
import nextId from 'react-id-generator';
import AuthorList from './AuthorList';
import Autocomplete from './Autocomplete';
import Navigator from './Navigator';
import InlineField from './InlineField';
import Error from '../Error';
/**Handles UI forms to add a new book and assign authors to it in 3 steps format.
 * step 1: a form for enter book's title.
 * step 2: an autocompletion form to assign existing authors to the new book.
 * step 3: a form to assign new (i.e. non-existing) authors to the new book (with submission to backend).
 * @returns 3 steps UI described above.
 */
export default function() {
    //authors data from backend:
    const [authorsData, setAuthorsData] = useState([]);
    //UI filling form step (1, 2, 3):
    const [step, setStep] = useState(1);
    //the new book's title:
    const [title, setTitle] = useState("");
    //existing authors to be assigned to the new book:
    const [existingAuthors, assignExistingAuthors] = useState([]);
    //new (i.e. non-existing authors) to be assigned to the new book:
    const [newAuthors, assignNewAuthors] = useState([]);
    //currently selected existing author:
    const [currentAuthor, setCurrentAuthor] = useState({});
    //currently entered new author (need their first and last name):
    const [firstName, setFirstName] = useState('');
    const [lastName, setLastName] = useState('');
    //in case there is any error occured in the backend:
    const [error, setError] = useState(null);
    //dont allow go to next step if title is empty:
    const allowNext = title.length !== 0;
    /*removing item from array adapted from :
     https://stackoverflow.com/questions/57341541/removing-object-from-array-using-hooks-usestate
      */
    const onExistingAuthorRemove = removeID => {
        console.log(removeID, 'ID RECORDED');
        assignExistingAuthors(
            existingAuthors.filter(item => item.authorID !== removeID)
        );
    };
    //for step 2's UI loading existing author list:
    const loading = authorsData.length === 0;
    //for new authors, as they don't have an ID, we assign fakeID by nextId() for removal purposes only:
    const onNewAuthorRemove = removeID => {
        assignNewAuthors(newAuthors.filter(item => item.authorID !== removeID));
    };
    //for sending data to backend:
    const onSubmit = e => {
        e.preventDefault();
        //make sure we have at least 1 author:
        if (existingAuthors.length + newAuthors.length === 0 ){
            setError('Error : Must assign at least 1 author to: ' + title);
            return;
        }
        //if we have, then remove duplicates in existing authors:
        const uniqueExistingAuthors = Array.from(new Set(existingAuthors));
        //now, submit data to backend:
        console.log(uniqueExistingAuthors, 'existingAuthors');
        Axios.post('/api/books', {
            existingAuthors : uniqueExistingAuthors,
            newAuthors,
            title
        }).then(res => {
            console.log(res, 'RES');
            //reload page upon successful creation:
            if (res.status === 201){
                window.location.reload();

            }
            //error occured, warn the user:
            else{
                setError('Error : ' + res.status + ' ' + res.statusText + '\n' + res.data.error);
            }
            //error may occur, warn the user:
        }).catch(e => {
            setError("Error: " + JSON.stringify(e.message));
        });
    };
    //sets the header for each step:
    const headers = () => {
        switch (step) {
            case 1:
                return 'Enter Book Title';
            case 2:
                return 'Assign existing authors to: ' + title;
            case 3:
                return 'Add new authors to the database and assign them to: ' + title;
        }
    };
    //sets the form input fields for each step:
    const inputField = () => {
        switch (step) {
            //asks for the book's title:
            case 1:
                return (
                    <Form.Control
                        type="text"
                        placeholder="Please enter the book's title"
                        required
                        onChange={e => setTitle(e.target.value)}

                    />
                );
            //provides existing authors:
            case 2:
                return (
                    <ButtonGroup>
                        <Autocomplete
                            data={authorsData}
                            loading={loading}
                            onChange={setCurrentAuthor}
                        />
                        <Button
                            variant="primary"
                            onClick={e =>
                                assignExistingAuthors([
                                    ...existingAuthors,
                                    currentAuthor
                                ])
                            }
                        >
                            Assign
                        </Button>
                    </ButtonGroup>
                );
            //asks for new author's firstName and lastName:
            //TODO onclick validation?
            case 3:
                return (
                    <InlineField
                        setFirstName={setFirstName}
                        setLastName={setLastName}
                        buttonName="Assign"

                        onClick={e =>
                            assignNewAuthors([
                                ...newAuthors,
                                { ID: nextId(), firstName, lastName }
                            ])
                        }
                        required={false}
                    />
                );
        }
    };
    //initially, fetch the existing authors list for 2nd step:
    useEffect(() => {
        Axios.get('/api/books').then(res => {
            console.log('Main', res);
            setAuthorsData(res.data);
            setStatus('done');
        });
    }, [status]);

    return (
        <Form onSubmit={onSubmit}>
            <h2>{headers()}</h2>
            {inputField()}

            {//shows the authors that have been assigned to the new book:
            step !== 1 ? (
                <AuthorList
                    step={step}
                    onNewAuthorRemove={onNewAuthorRemove}
                    onExistingAuthorRemove={onExistingAuthorRemove}
                    newAuthors={newAuthors}
                    existingAuthors={existingAuthors}
                />
            ) : null}
            <Navigator step={step} min={1} max={3} setStep={setStep} allowNext={allowNext} />
            {//renders submit button on the last step:
            step === 3 ? (
                <Button variant="primary" type="submit" >
                    {' '}
                    Submit{' '}
                </Button>
            ) : null}
            {error
            ?  <Error message={error}/>
            : null}
        </Form>
    );
}
