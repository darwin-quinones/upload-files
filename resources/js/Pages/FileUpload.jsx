import '../bootstrap';
import '../../css/app.css';

import React, { useState, useEffect } from "react";

export default function Dashboard() {
    const [files, setFiles] = useState('');
    //state for checking file size
    const [fileSize, setFileSize] = useState(true);
    const [filesNumber, setFilesNumber] = useState(true)
    let [totalFilesSize, setTotalFilesSize] = useState(0)
    let allowedFilesSize = 50
    let [progress, setProgress] = useState(0)
    let [elapsedTime, setElapsedTime] = useState(0)
    // let [canCalculateTime, setCancalculateTime] = useState(true)
    let canCalculateTime = true
    let [calculateTotalFileSize, setCalculateTotalFileSize] = useState(0.1)
    //base end point url
    const FILE_UPLOAD_BASE_ENDPOINT = "http://localhost:8000";

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

    const fileSubmitHandler = (event) => {
        event.preventDefault();
        setFileSize(true);
        setProgress(1)

        const formData = new FormData();

        //let allowedFilesSize = 50 // 10 MB
        let allowedFilesNumber = 20
        for (let i = 0; i < files.length; i++) {
            var filesize = files[i].size / 1024;
            filesize = (Math.round((filesize / 1024) * 100) / 100);
            setCalculateTotalFileSize(calculateTotalFileSize = calculateTotalFileSize + filesize);
            //console.log('calculateTotalFileSize: ', calculateTotalFileSize)
            //formData.append('files', files[i])
            formData.append(files[i].name, files[i])
        }
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
        fetch(FILE_UPLOAD_BASE_ENDPOINT + '/file-upload', requestOptions)
            .then(async response => {
                // const isJson = response.headers.get('content-type')?.includes('application/json');
                const data = await response.json();
                console.log(response.ok);
                // check for error response
                if (response.ok && data) {
                    // console.log('cambiar el estado: ', data);
                    setProgress(100)
                    console.log('canCalculateTime', canCalculateTime)
                    canCalculateTime = false
                    console.log('canCalculateTime', canCalculateTime)
                    alert('archivo cargado con exito')
                }
                if (!response.ok) {
                    // get error message
                    const error = (data && data.message) || response.status;
                    return Promise.reject(error);

                }
            })
            .catch(error => {
                canCalculateTime = false
                setProgress(0)
                console.error('Error while uploading file!', error);
            })


        setInterval(() => {
            if (canCalculateTime) {
                console.log('canCalculateTime: ', canCalculateTime)

                setElapsedTime(elapsedTime = elapsedTime + 5)
                // let megasBySeconds = allowedFilesSize / estimatedTimeToUploadFile
                let megasBySeconds = 0.061714285714 // uploads 0.061714285714 per
                let megaBytesUploaded = elapsedTime * megasBySeconds
                let percent = parseInt(megaBytesUploaded / calculateTotalFileSize * 100)
                if (percent < 98) {
                    setProgress(percent)
                } else {
                    setProgress(98)
                }
                // percent = calculatePercent - 100


                // console.log('totalFilesSize: ', totalFilesSize)
                console.log('percent: ', percent)
                console.log('megaBytesUploaded', megaBytesUploaded)
                console.log('calculateTotalFileSize: ', calculateTotalFileSize)
                console.log('megasBySeconds: ', megasBySeconds)
                console.log('elapsedTime: ', elapsedTime)
                // console.log('progress: ', progress)

                console.log('allowedFilesSize: ', allowedFilesSize)
            } else {
                return;
            }

        }, 1000)



    }

    return (

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

    );
}
