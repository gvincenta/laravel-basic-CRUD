import React, { useState, useEffect } from 'react';
import { Button, Form, Row, Col } from 'react-bootstrap';
import Axios from 'axios';
import ReactTable from 'react-table-6';
import 'react-table-6/react-table.css';
import Spinner from './Spinner';
import InlineField from './Books/InlineField';
import Alert from './Alert';
/**
 * Displays Books and Authors table
 * with functionality: deleting a book and changing an author's name.
 * @param props.data : the data to be displayed.
 * @param props.status : whether to carry out initial data fetch or not.
 *
 */
export default function(props) {
    const [data, setData] = useState(props.data || null);
    const [status, setStatus] = useState(props.status || 'loading');
    //for changing an author's name: displays their old firstName and lastName
    const [oldFirstName, setOldFirstName] = useState('');
    const [oldLastName, setOldLastName] = useState('');
    //for changing an author's name: prompts for their new firstName and lastName
    const [newFirstName, setNewFirstName] = useState('');
    const [newLastName, setNewLastName] = useState('');
    //for changing an author's name: remember their ID for applying changes in backend.
    const [authorID, setAuthorID] = useState(null);
    //for error messages or delete messages:
    const [message, setMessage] = useState(null);
    //handles delete button:
    const displayDeleteButton = props => {
        //if the cell is not empty, then render a delete button:
        if (props.value) {
            return (
                <div>
                    {props.value} &nbsp;
                    <Button
                        data-step="5"
                        data-intro="Click me to delete this book. WARNING: this will be directly applied to backend."
                        variant="danger"
                        onClick={e => {
                            e.preventDefault();
                            //refresh error message:
                            setMessage(null);
                            console.log(props.value, 'ONCLICK');
                            const { title, bookID } = props.original;
                            //tell the user to wait while we are deleting this book:
                            setMessage(
                                'deleting: ' +
                                    title +
                                    ' with bookID : ' +
                                    bookID +
                                    ' please wait... '
                            );
                            setStatus('deleting');

                            //deletes this book in the backend:
                            Axios.delete('/api/books', {
                                data: { bookID: props.value }
                            })
                                .then(res => {
                                    console.log(res, 'AFTER DELETE');
                                    //handle success / failure:
                                    window.location.reload();
                                })
                                .catch(e => {
                                    setMessage(
                                        'Error: ' + JSON.stringify(e.message)
                                    );
                                    setStatus('done');
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
                        data-step="6"
                        data-intro={
                            "Click me to change this author's name. A form will appear below the table " +
                            'to change their name.'
                        }
                        onClick={e => {
                            e.preventDefault();
                            setMessage(null);
                            //remembers the author's existing data, then display a form:
                            setAuthorID(props.value);
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
            Header: 'BookID',
            accessor: 'bookID',
            Cell: props => displayDeleteButton(props)
        },
        { Header: 'Title', accessor: 'title' },
        {
            Header: 'AuthorID',
            accessor: 'authorID',
            Cell: props => displayUpdateButton(props)
        },
        { Header: 'First Name', accessor: 'firstName' },
        { Header: 'Last Name', accessor: 'lastName' }
    ];
    //at start (only), fetch books and authors from backend:
    useEffect(() => {
        Axios.get('/api/books')
            .then(res => {
                setData(res.data);
                setStatus('done');
            })
            .catch(e => {
                setMessage('Error: ' + JSON.stringify(e.message));
                setStatus('done');
            });
    }, ['initialOnly']);

    //display books and authors data:
    if (status === 'loading') {
        //display loading animation if data hasn't been fetched yet:
        return <Spinner />;
    }
    if (status === 'deleting') {
        return (
            <div>
                <Alert message={message} variant={'info'} />
                <Spinner />
            </div>
        );
    }
    return (
        <div>
            <ReactTable
                data={data || []}
                columns={columns}
                defaultPageSize={5}
            />
            {status === 'changing' ? (
                <Form
                    onSubmit={e => {
                        //avoid reloading:
                        e.preventDefault();
                        console.log('SUBMIT RUNN');
                        //update author's name:
                        Axios.put('/api/authors', {
                            authorID,
                            firstName: newFirstName,
                            lastName: newLastName
                        })
                            .then(res => {
                                console.log('res', res);
                                window.location.reload();
                            })
                            .catch(e => {
                                setMessage(
                                    'Error: ' + JSON.stringify(e.message)
                                );
                            });
                    }}
                >
                    <Form.Text className="text-muted">
                        Changing Author with authorID : {authorID} and name :{' '}
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
            {message ? ( //display error when it occurs:
                <Alert message={message} />
            ) : null}
        </div>
    );
}
