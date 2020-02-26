import React, { useState, useEffect, useLayoutEffect } from 'react';
import Axios from 'axios';
import { Button, Row, Col, Form } from 'react-bootstrap';
import XMLViewer from 'react-xml-viewer';
import Spinner from './Spinner';
import FileDownload from 'js-file-download';
import { CSVLink, CSVDownload } from 'react-csv';
import { CsvToHtmlTable } from 'react-csv-to-table';
import Alert from './Alert';
import Navigator from './Books/Navigator';
/** Handles exporting data to XML and CSV */
export default function() {
    //export to either XML or CSV:
    const [type, setType] = useState('');
    //books / authors / authors and books content:
    const [content, setContent] = useState('');
    //UI status:
    const [status, setStatus] = useState('');
    //data from backend:
    const [data, setData] = useState(null);
    //url for fetching data from backend:
    const [url, setURL] = useState('');
    //for error messages:
    const [error, setError] = useState(null);
    //for display heading:
    const [downloadedContent, setDownloadedContent] = useState("");
    //remember what file (XML / CSV) you downloaded:
    const [downloadedType, setDownloadedType] = useState("");
    //step 1 : display forms, step 2: diplay downloaded content.
    const [step,setStep] = useState(1);

    const allowNext =  data !== null;

    //displays loading spinner:
    if (status === 'loading') {
        return <Spinner />;
    }
    //displays the xml with download option:
    if (status === 'done' && downloadedType === 'xml' && step === 2) {
        return (
            <div>
                <h2> Displaying {downloadedContent} XML </h2>
                <XMLViewer xml={data} />
                <a href="" onClick={e => {e.preventDefault();setStatus('download');}}>
                    {' '}
                    Download me (in compact format){' '}
                </a>
                <br/>
                <Navigator step={step} min={1} max={2} setStep={setStep} allowNext={allowNext} />
            </div>
        );
    }
    //displays the csv file with download option:
    if (status === 'done' && downloadedType === 'csv'  && step === 2) {
        return (
            <div>
                <h2> Displaying {downloadedContent} CSV as a plain text : </h2>

                <CsvToHtmlTable data={data} csvDelimiter="," />

                <CSVLink data={data}>Download me</CSVLink>
                <br/>
                <Navigator step={step} min={1} max={2} setStep={setStep} allowNext={allowNext} />
        </div>
        );
    }
    //downloads the xml file:
    if (status === 'download' && downloadedType === 'xml'  && step === 2) {
        setStatus("done");
        return FileDownload(data, content + '.xml');
    }

    //displays the form to choose what content and type is to be exported:
    return (
        <Form
            onSubmit={e => {
                e.preventDefault();
                //for displaying spinner:
                setStatus('loading');
                //sets URL properly:
                var url = '/api/' + content + '/export/' + type.toUpperCase();
                if (content === 'authors and books') {
                    url =
                        '/api/authors/export/' +
                        type.toUpperCase() +
                        '/with-books';
                }
                console.log('URL', url);
                setURL(url);
                //fetching data from database:
                Axios.get(url)
                    .then(res => {
                        //data is ready:
                        setData(res.data);
                        setDownloadedContent(content);
                        setDownloadedType(type);
                        setStatus('done');
                        setStep(2);

                    })
                    .catch(e => {
                        setStatus('error');
                        setError('Error: ' + JSON.stringify(e.message));
                    });
            }}
        >
            <fieldset>
                <Form.Group as={Row}>
                    <Form.Label as="type" column sm={2}>
                        Export to:
                    </Form.Label>
                    <Col>
                        <Form.Check
                            type="radio"
                            label="XML"
                            name="type"
                            id="xml"
                            onClick={e => setType(e.target.id)}
                            required
                        />
                        <Form.Check
                            type="radio"
                            label="CSV"
                            name="type"
                            id="csv"
                            onClick={e => setType(e.target.id)}
                        />
                    </Col>
                </Form.Group>
            </fieldset>
            <fieldset>
                <Form.Group as={Row}>
                    <Form.Label as="content" column sm={2}>
                        Radios
                    </Form.Label>
                    <Col>
                        <Form.Check
                            type="radio"
                            label="books only"
                            name="content"
                            id="books"
                            onClick={e => setContent(e.target.id)}
                        />
                        <Form.Check
                            type="radio"
                            label="authors only"
                            name="content"
                            id="authors"
                            onClick={e => setContent(e.target.id)}
                        />
                        <Form.Check
                            type="radio"
                            label="authors and books"
                            name="content"
                            id="authors and books"
                            onClick={e => setContent(e.target.id)}
                            required
                        />
                    </Col>
                </Form.Group>
            </fieldset>
            <Button variant="primary" type="submit">
                {' '}
                export {' '}
            </Button>
            <Navigator step={step} min={1} max={2} setStep={setStep} allowNext={allowNext} />
            {error ? ( //display error when it occurs:
                <Alert message={error} />
            ) : null}
        </Form>
    );
}
