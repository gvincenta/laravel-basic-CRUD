import React, { useState, useEffect } from 'react';
import { Button, Form, Row, Col } from 'react-bootstrap';
import Axios from 'axios';
import ReactTable from 'react-table-6';
import 'react-table-6/react-table.css';
import Spinner from '../Spinner';
import InlineField from './InlineField';

/**
 * Displays Books and Authors table
 * with functionality: deleting a book and changing an author's name.
 * @param props.data : the data to be displayed.
 * @param props.status : whether to carry out initial data fetch or not.
 *
 */
export default function(props) {
    const [data, setData] = useState(props.data || []);
    const [status, setStatus] = useState(props.status || 'loading');
    //for changing an author's name: displays their old firstName and lastName
    const [oldFirstName, setOldFirstName] = useState('');
    const [oldLastName, setOldLastName] = useState('');
    //for changing an author's name: prompts for their new firstName and lastName
    const [newFirstName, setNewFirstName] = useState('');
    const [newLastName, setNewLastName] = useState('');
    //for changing an author's name: remember their ID for applying changes in backend.
    const [ID, setID] = useState(null);
    //handles delete button:
    const displayDeleteButton = props => {
        //if the cell is not empty, then render a delete button:
        if (props.value) {
            return (
                <div>
                    {props.value} &nbsp;
                    <Button
                        variant="danger"
                        onClick={e => {
                            e.preventDefault();
                            console.log(props.value, 'ONCLICK');
                            //deletes this book in the backend:
                            Axios.delete('/api/books', {
                                data: { ID: props.value }
                            }).then(res => {
                                console.log(res, 'AFTER DELETE');
                                //handle success / failure:
                                window.location.reload();
                            });
                        }}
                    >
                        delete
                    </Button>
                </div>
            );
        } else {
            return <div> </div>;
        }
    };
    //displays a button for changing an author's name
    const displayUpdateButton = props => {
        //displays button only when the cell is not empty
        if (props.value) {
            return (
                <div>
                    {props.value} &nbsp;
                    <Button
                        onClick={e => {
                            e.preventDefault();
                            //remembers the author's existing data, then display a form:
                            setID(props.value);
                            setOldFirstName(props.original.firstName);
                            setOldLastName(props.original.lastName);
                            setStatus('changing');
                        }}
                    >
                        change
                    </Button>
                </div>
            );
        } else {
            return <div> </div>;
        }
    };
    //the columns to be rendered
    const columns = [
        {
            Header: 'bookID',
            accessor: 'books_ID',
            Cell: props => displayDeleteButton(props)
        },
        { Header: 'Title', accessor: 'title' },
        {
            Header: 'authorID',
            accessor: 'ID',
            Cell: props => displayUpdateButton(props)
        },
        { Header: 'firstName', accessor: 'firstName' },
        { Header: 'lastName', accessor: 'lastName' }
    ];
    //initially, fetch books and authors from backend:
    useEffect(() => {
        if (status === 'loading') {
            Axios.get('/api/books').then(res => {
                setData(res.data);
                setStatus('done');
            });
        }
    }, [status]);

    //display books and authors data:
    if (data.length > 0) {
        return (
            <div>
                <ReactTable data={data} columns={columns} defaultPageSize={5} />
                {status === 'changing' ? (
                    <Form
                        onSubmit={e => {
                            //avoid reloading:
                            e.preventDefault();
                            //update author's name:
                            Axios.put('/api/authors', {
                                ID,
                                firstName: newFirstName,
                                lastName: newLastName
                            }).then(res => {
                                console.log('res', res);
                                //TODO: CHANGE THIS HANDLING
                                if (res.data.affectedRows == 1) {
                                    //sucessfully changed an author's name, re-fetch data again:
                                    setMessage('succeed!');
                                    setStatus('loading..');
                                    window.location.reload();
                                } else {
                                    setMessage('failed');
                                }
                            });
                        }}
                    >
                        <Form.Text className="text-muted">
                            Changing Author with ID : {ID} and name :{' '}
                            {oldFirstName + ' ' + oldLastName}
                        </Form.Text>

                        <InlineField
                            buttonName="Submit"
                            setFirstName={setNewFirstName}
                            setLastName={setNewLastName}
                            buttonType="submit"
                            required={true}
                        />
                    </Form>
                ) : null}
            </div>
        );
    }

    //display loading animation if data hasn't been fetched yet:
    return <Spinner />;
}
