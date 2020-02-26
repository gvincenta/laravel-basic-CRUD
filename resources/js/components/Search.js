import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import Axios from 'axios';
import { CsvToHtmlTable } from 'react-csv-to-table';
import Spinner from './Spinner';
import Table from './Table';
import { Button, Row, Col, Form } from 'react-bootstrap';
import Alert from './Alert';

/** for searching a book by its title / author: */

export default function() {
    //for a search by book's title:
    const [title, setTitle] = useState('');
    //for a search by book's author:
    const [firstName, setFirstName] = useState('');
    const [lastName, setLastName] = useState('');
    //for storing search results from backend:
    const [data, setData] = useState(null);
    //specify by title / author:
    const [by, setBy] = useState(null);
    //fetching status:
    const [status, setStatus] = useState('');
    const [searched, setSearched] = useState('');
    const [error, setError] = useState(null);
    //fetching data from backend:
    const getData = (e, searchBy, setStatus) => {
        e.preventDefault();
        //empty error message:
        setError(null);
        //load spinning UI:
        setStatus('loading');
        if (searchBy === 'title') {
            //request for a search:
            Axios.get('/api/books/with-filter', {
                params: {
                    title
                }
            })
                .then(res => {
                    //display data:
                    setData(res.data);
                    setBy(searchBy);
                    setSearched('book title: ' + title);
                    setStatus('done');
                })
                .catch(e => {
                    //catch any errors:
                    setError('Error: ' + JSON.stringify(e.message));
                    setStatus('done');
                });
        } else if (searchBy === 'author') {
            //request for a search:
            Axios.get('/api/authors/with-filter', {
                params: {
                    firstName,
                    lastName
                }
            })
                .then(res => {
                    //display data:
                    setData(res.data);
                    setBy(searchBy);
                    setSearched('author: ' + firstName + ' ' + lastName);

                    setStatus('done');
                })
                .catch(e => {
                    //catch any errors:
                    setError('Error: ' + JSON.stringify(e.message));
                    setStatus('done');
                });
        }
    };
    //shows loading UI:
    if (status === 'loading') {
        return <Spinner />;
    }
    //returns a form for searching by title / author:
    //by default, also return Books and Authors table:
    return (
        <div>
            <br />

            <Form
                onSubmit={e => {
                    getData(e, 'title', setStatus);
                }}
            >
                <Row>
                    <Col sm="10">
                        <Form.Control
                            type="text"
                            placeholder="Title (not case sensitive)"
                            required
                            onChange={e => setTitle(e.target.value)}
                            data-step="3"
                            data-intro="Enter something here for searching a book by its title."
                        />
                    </Col>
                    <Button variant="primary" type="submit">
                        {' '}
                        Search by title
                    </Button>
                </Row>
            </Form>
            <br />
            <Form
                onSubmit={e => {
                    getData(e, 'author', setStatus);
                }}
            >
                <Row>
                    <Col sm="5">
                        <Form.Control
                            type="text"
                            placeholder="First Name (not case sensitive)"
                            data-step="4"
                            data-intro={
                                'Or, enter here for searching a book by its author. ' +
                                'For mononymous names, enter their first name in the "Last Name" field as well.'
                            }
                            onChange={e => setFirstName(e.target.value)}
                            required
                        />
                    </Col>
                    <Col sm="5">
                        <Form.Control
                            type="text"
                            placeholder="Last Name (not case sensitive)"
                            required
                            onChange={e => setLastName(e.target.value)}
                        />
                    </Col>
                    <Button variant="primary" type="submit">
                        {' '}
                        Search by author{' '}
                    </Button>
                </Row>
            </Form>
            <br />

            {data ? ( //displays search result after fetching data from backend:
                <div>
                    <h2> Search results for {searched}</h2>
                    <Table data={data} status="done" />
                </div>
            ) : (
                //otherwise, display Books and Authors Table
                <Table />
            )}
            {error ? ( //display error when it occurs:
                <Alert message={error} />
            ) : null}
        </div>
    );
}
