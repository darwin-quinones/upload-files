
import React, { useState, useEffect } from 'react';
import axios from 'axios';
import '../bootstrap';





export default function ProgressBarExample1() {
    //base end point url
    //const FILE_UPLOAD_BASE_ENDPOINT = "http://127.0.0.1:8020/file-upload";
    const URL_UPLOAD = 'http://localhost:8000/upload-files'
    const URL_PROCESS = 'http://localhost:8000/process-file-uploaded'

    const [files, setFiles] = useState('');
    //state for checking file size
    const [fileSize, setFileSize] = useState(true);
    const [filesNumber, setFilesNumber] = useState(true)
    let [totalFilesSize, setTotalFilesSize] = useState(0)
    let allowedFilesSize = 50
    //let [progress, setProgress] = useState(0)
    let [elapsedTime, setElapsedTime] = useState(0)
    // let [canCalculateTime, setCancalculateTime] = useState(true)
    let canCalculateTime = true
    let [calculateTotalFileSize, setCalculateTotalFileSize] = useState(0.1)
    //base end point url
    const FILE_UPLOAD_BASE_ENDPOINT = "http://localhost:8000";

    const [progress, setProgress] = useState(0);

    const uploadFileHandler = (event) => {
        setFiles(event.target.files);
    };

    const getColor = () => {
        if (progress < 40) {
            return '#ff0000'
        } else if (progress < 70) {
            return '#ffa500'
        } else {
            return '#2ecc71'
        }
    }

    const getEventSource = (id_tabla_ruta, totalFileSize) => {
        const URL_PROCESS_FILES_WITH_DATA = URL_PROCESS + `?id_tabla_ruta=${id_tabla_ruta}&total_files_size=${totalFileSize}`
        var evtSource = new EventSource(URL_PROCESS_FILES_WITH_DATA);

        evtSource.addEventListener("message", function (e) {

            var obj = JSON.parse(e.data);
            console.log(obj.progress);
            setProgress(obj.progress);
            // newElement.innerHTML = "ping at " + obj.time;
            // eventList.appendChild(newElement);
        }, false);

        evtSource.addEventListener('error', (event) => {
            console.log(event);
            evtSource.close();
        });

        return () => {
            evtSource.close();
        };
    }
    useEffect(() => {
        //getEventSource()

    }, []);

    const fileSubmitHandler = (event) => {
        event.preventDefault();
        setFileSize(true);

        const formData = new FormData();

        //let allowedFilesSize = 50 // 10 MB
        let allowedFilesNumber = 20
        for (let i = 0; i < files.length; i++) {
            // var filesize = files[i].size / 1024;
            var filesize = files[i].size;
            setCalculateTotalFileSize(calculateTotalFileSize += filesize);
            //filesize2 = (Math.round((filesize / 1024) * 100) / 100);

            //console.log('calculateTotalFileSize: ', calculateTotalFileSize)
            //formData.append('files', files[i])
            formData.append(files[i].name, files[i])

        }
        // formData.append('totalFilesSize', calculateTotalFileSize)
        // console.log('calculateTotalFileSize: ', calculateTotalFileSize)
        // console.log('formData: ', formData)
        setTotalFilesSize(20)

        if (totalFilesSize > allowedFilesSize) {
            setFileSize(false);
            return;
        } if (files.length > allowedFilesNumber) {
            setFilesNumber(false)
        }
        const requestOptions = {
            method: 'POST',
            body: formData
        };
        fetch(FILE_UPLOAD_BASE_ENDPOINT + '/upload-files', requestOptions)
            .then(async response => {
                // const isJson = response.headers.get('content-type')?.includes('application/json');
                const data = await response.json();
                console.log(data);
                console.log(response)
                // check for error response
                if (data.success) {
                    getEventSource(data.id_tabla_ruta, calculateTotalFileSize)
                } else {
                    console.log('something went wrong')
                }
            })
            .catch(error => {
                canCalculateTime = false
                setProgress(0)
                console.error('Error while uploading file!', error);
            })
    }
    return (
        <div>
            <form onSubmit={fileSubmitHandler}>
                <input type="file" multiple onChange={uploadFileHandler} />
                <button type='submit'>Upload</button>
                {!fileSize && <p style={{ color: 'red' }}>File size exceeded!!</p>}
                {!filesNumber && <p style={{ color: 'red' }}>Files Number exceeded!!</p>}

                <div className="progress-bar">
                    <div className="progress-bar-fill" style={{ width: `${progress}%`, backgroundColor: getColor() }}></div>
                </div>
                <div className="progress-label">{progress}%</div>

            </form>
        </div>
    );
};


